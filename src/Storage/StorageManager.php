<?php
namespace Burzum\FileStorage\Storage;

use InvalidArgumentException;
use RuntimeException;

/**
 * StorageManager - manages and instantiates Gaufrette storage engine instances
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
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
	}

	/**
	 * Get an adapter config
	 *
	 * @param string $configName Configuration name
	 * @return array
	 */
	public static function getConfig($configName) {
		$_this = StorageManager::getInstance();

		if (!isset($_this->_adapterConfig[$configName])) {
			throw new RuntimeException(sprintf('Storage adapter config `%s` does not exist', $configName));
		}

		return $_this->_adapterConfig[$configName];
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
	 */
	public static function adapter($adapterName, $renewObject = false) {
		return self::getAdapter($adapterName, $renewObject);
	}

	/**
	 * Gets a configured instance of a storage adapter.
	 *
	 * @param mixed $adapterName string of adapter configuration or array of settings
	 * @param boolean $renewObject Creates a new instance of the given adapter in the configuration
	 * @throws \RuntimeException
	 * @return \Gaufrette\Filesystem
	 */
	public static function getAdapter($adapterName, $renewObject = false) {
		$_this = StorageManager::getInstance();

		$isConfigured = true;
		if (is_string($adapterName)) {
			if (!empty($_this->_adapterConfig[$adapterName])) {
				$adapter = $_this->_adapterConfig[$adapterName];
			} else {
				throw new RuntimeException(sprintf('Invalid Storage Adapter %s!', $adapterName));
			}

			if (!empty($_this->_adapterConfig[$adapterName]['object']) && $renewObject === false) {
				return $_this->_adapterConfig[$adapterName]['object'];
			}
		}

		if (is_array($adapterName)) {
			$adapter = $adapterName;
			$isConfigured = false;
		}

		$class = $adapter['adapterClass'];
		$Reflection = new \ReflectionClass($class);
		if (!is_array($adapter['adapterOptions'])) {
			throw new InvalidArgumentException(sprintf('%s: The adapter options must be an array!', $adapterName));
		}

		$adapterObject = $Reflection->newInstanceArgs($adapter['adapterOptions']);
		$engineObject = new $adapter['class']($adapterObject);
		if ($isConfigured) {
			$_this->_adapterConfig[$adapterName]['object'] = &$engineObject;
		}

		return $engineObject;
	}
}
