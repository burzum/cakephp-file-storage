<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage;

/**
 * Use the StorageTrait for convenient access to the storage adapters in any class.
 */
trait StorageTrait {

/**
 * Wrapper around the singleton call to StorageManager::config
 *
 * Makes it easy to mock the adapter in tests.
 *
 * @param string $configName
 * @return array
 */
	public function storageConfig($configName) {
		return StorageManager::config($configName);
	}

/**
 * Wrapper around the singleton call to StorageManager::config
 *
 * Makes it easy to mock the adapter in tests.
 *
 * @param string $configName
 * @return array
 */
	public function storageAdapter($configName, $renewObject = false) {
		return StorageManager::adapter($configName, $renewObject);
	}

/**
 * Wrapper around the singleton call to StorageManager::config
 *
 * Makes it easy to mock the adapter in tests.
 *
 * @param string $configName
 * @return Object
 */
	public function storageManager() {
		return StorageManager::getInstance();
	}
}
