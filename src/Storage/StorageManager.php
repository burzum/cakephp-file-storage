<?php
namespace Burzum\FileStorage\Storage;

use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;

/**
 * StorageManager - manages and instantiates Gaufrette storage engine instances
 *
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
class StorageManager {

	/**
	 * Adapter configurations
	 *
	 * @var array
	 */
	protected $_adapterConfig = [
		'Local' => [
			'adapterOptions' => [TMP, true],
			'adapterClass' => '\Gaufrette\Adapter\Local',
			'class' => '\Gaufrette\Filesystem'
		]
	];

	/**
	 * Adapter objects
	 *
	 * @var array
	 */
	protected $_adapterInstances = [];

	/**
	 * Return a singleton instance of the StorageManager.
	 *
	 * @return \Burzum\FileStorage\Storage\StorageManager
	 */
	public static function &getInstance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] = new StorageManager();
		}

		return $instance[0];
	}

	/**
	 * Gets the configuration array for an adapter.
	 *
	 * @param string $adapter
	 * @param array $options
	 * @return mixed
	 * @deprecated Use StorageManager::getConfig() and setConfig()
	 */
	public static function config($adapter, $options = array()) {
		if (!empty($adapter) && !empty($options)) {
			self::setConfig($adapter, $options);
			$options;
		}

		try {
			return self::getConfig($adapter);
		} catch (RuntimeException $e) {
			return false;
		}
	}

	/**
	 * Sets an adapter config
	 *
	 * @param string $configName Config name
	 * @param array $configOptions Options
	 * @return null
	 */
	public static function setConfig($configName, array $configOptions) {
		$_this = StorageManager::getInstance();
		$_this->_adapterConfig[$configName] = $configOptions;
		unset($_this->_adapterInstances[$configName]);
	}

	/**
	 * Get an adapter config
	 *
	 * @param string $name Configuration name
	 * @return array
	 */
	public static function getConfig($name) {
		$_this = StorageManager::getInstance();

		if (!isset($_this->_adapterConfig[$name])) {
			throw new RuntimeException(sprintf('Storage adapter config `%s` does not exist', $name));
		}

		return $_this->_adapterConfig[$name];
	}

	/**
	 * Flush all or a single adapter from the config.
	 *
	 * @param string $name Config name, if none all adapters are flushed.
	 * @return bool True on success.
	 */
	public static function flush($name = null) {
		$_this = StorageManager::getInstance();

		if (isset($_this->_adapterConfig[$name])) {
			unset($_this->_adapterConfig[$name]);
			unset($_this->_adapterInstances[$name]);
			return true;
		}

		return false;
	}

	/**
	 * Gets a configured instance of a storage adapter.
	 *
	 * @param mixed $adapterName string of adapter configuration or array of settings
	 * @param boolean $renewObject Creates a new instance of the given adapter in the configuration
	 * @throws \RuntimeException
	 * @return \Gaufrette\Filesystem
	 * @deprecated Use StorageManager::getAdapter() instead
	 */
	public static function adapter($adapterName, $renewObject = false) {
		return self::getAdapter($adapterName, $renewObject);
	}

	/**
	 * Gets a configured instance of a storage adapter.
	 *
	 * @param mixed $name string of adapter configuration or array of settings
	 * @param boolean $renewObject Creates a new instance of the given adapter in the configuration
	 * @throws \RuntimeException
	 * @return \Gaufrette\Filesystem
	 */
	public static function getAdapter($name, $renewObject = false) {
		$_this = StorageManager::getInstance();

		if (is_string($name)) {
			if (!empty($_this->_adapterInstances[$name]) && $renewObject === false) {
				return $_this->_adapterInstances[$name];
			}

			if (!empty($_this->_adapterConfig[$name])) {
				$adapter = $_this->_adapterConfig[$name];
			} else {
				throw new RuntimeException(sprintf('Invalid Storage Adapter %s!', $name));
			}
		}

		if (is_array($name)) {
			$adapter = $name;
		}

		$class = $adapter['adapterClass'];
		$Reflection = new ReflectionClass($class);
		if (!is_array($adapter['adapterOptions'])) {
			throw new InvalidArgumentException(sprintf('%s: The adapter options must be an array!', $name));
		}

		$adapterObject = $Reflection->newInstanceArgs($adapter['adapterOptions']);
		$engineObject = new $adapter['class']($adapterObject);
		$_this->_adapterInstances[$name] = &$engineObject;

		return $engineObject;
	}

	/**
	 * Returns an array that can be used to describe the internal state of this
	 * object.
	 *
	 * @return array
	 */
	public function __debugInfo() {
		$_this = StorageManager::getInstance();
		return [
			'_adapterConfig' => $_this->_adapterConfig,
			'_adapterInstances' => $_this->_adapterInstances,
		];
	}
}
