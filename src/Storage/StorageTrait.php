<?php
namespace Burzum\FileStorage\Storage;

use Burzum\FileStorage\Lib\StorageManager;

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
	public function storageAdapter($configName) {
		return StorageManager::adapter($configName);
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
