<?php
namespace Burzum\FileStorage\Lib;

/**
 * StorageManager - manages and instantiates gaufrette storage engine instances
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class StorageManager {

/**
 * Adapter configs
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
 * @return ClassRegistry instance
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
 */
	public static function config($adapter, $options = array()) {
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
 * @throws RuntimeException
 * @return boolean True on success
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
 * StorageAdapter
 *
 * @param mixed $adapterName string of adapter configuration or array of settings
 * @param boolean $renewObject Creates a new instance of the given adapter in the configuration
 * @throws RuntimeException
 * @return Gaufrette object as configured by first argument
 */
	public static function adapter($adapterName, $renewObject = false) {
		$_this = StorageManager::getInstance();

		$isConfigured = true;
		if (is_string($adapterName)) {
			if (!empty($_this->_adapterConfig[$adapterName])) {
				$adapter = $_this->_adapterConfig[$adapterName];
			} else {
				throw new \RuntimeException(sprintf('Invalid Storage Adapter %s!', $adapterName));
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
			throw new \InvalidArgumentException(sprintf('%s: The adapter options must be an array!', $adapterName));
		}
		$adapterObject = $Reflection->newInstanceArgs($adapter['adapterOptions']);
		$engineObject = new $adapter['class']($adapterObject);
		if ($isConfigured) {
			$_this->_adapterConfig[$adapterName]['object'] = &$engineObject;
		}
		return $engineObject;
	}

}