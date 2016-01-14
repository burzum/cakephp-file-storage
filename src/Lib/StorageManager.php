<?php
namespace Burzum\FileStorage\Lib;

use Burzum\FileStorage\Storage\StorageManager as NewStorageManager;

/**
 * StorageManager - manages and instantiates gaufrette storage engine instances
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 *
 * @deprecated Use \Burzum\FileStorage\Storage\StorageManager instead.
 */
class StorageManager {

/**
 * Gets the configuration array for an adapter.
 *
 * @param string $adapter
 * @param array $options
 * @return mixed
 */
	public static function config($adapter, $options = array()) {
		return NewStorageManager::config($adapter, $options);
	}

/**
 * Flush all or a single adapter from the config.
 *
 * @param string $name Config name, if none all adapters are flushed.
 * @throws RuntimeException
 * @return boolean True on success
 */
	public static function flush($name = null) {
		return NewStorageManager::flush($name);
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
		return NewStorageManager::adapter($adapterName, $renewObject);
	}
}
