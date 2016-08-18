<?php
namespace Burzum\FileStorage\Storage;

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
	 * @throws \InvalidArgumentException
	 * @return mixed
	 */
	public static function config($adapter, $options = []) {
		$_this = StorageManager::getInstance();

		if (!empty($adapter) && !empty($options)) {
			return $_this->_adapterConfig[$adapter] = $options;
		}

		if (isset($_this->_adapterConfig[$adapter])) {
			return $_this->_adapterConfig[$adapter];
		}

		return false;
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
	 * @param mixed $configName string of adapter configuration or array of settings
	 * @param boolean $renewObject Creates a new instance of the given adapter in the configuration
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 * @return \Gaufrette\Filesystem
	 */
	public static function get($configName, $renewObject = false) {
		if (empty($configName) || !is_string($configName)) {
			throw new \InvalidArgumentException('StorageManager::get() first arg must be a non empty string!');
		}

		$_this = StorageManager::getInstance();

		$isConfigured = true;
		if (is_string($configName)) {
			if (!empty($_this->_adapterConfig[$configName])) {
				$adapter = $_this->_adapterConfig[$configName];
			} else {
				throw new \RuntimeException(sprintf('Invalid Storage Adapter %s!', $configName));
			}

			if (!empty($_this->_adapterConfig[$configName]['object']) && $renewObject === false) {
				return $_this->_adapterConfig[$configName]['object'];
			}
		}

		if (is_array($configName)) {
			$adapter = $configName;
			$isConfigured = false;
		}

		$class = $adapter['adapterClass'];
		$Reflection = new \ReflectionClass($class);
		if (!is_array($adapter['adapterOptions'])) {
			throw new \InvalidArgumentException(sprintf('%s: The adapter options must be an array!', $configName));
		}
		$adapterObject = $Reflection->newInstanceArgs($adapter['adapterOptions']);
		$engineObject = new $adapter['class']($adapterObject);
		if ($isConfigured) {
			$_this->_adapterConfig[$configName]['object'] = &$engineObject;
		}
		return $engineObject;
	}
}
