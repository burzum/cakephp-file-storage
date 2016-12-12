<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage;

/**
 * Use the StorageTrait for convenient access to the storage adapters in any class.
 */
trait StorageTrait {

	/**
	 * Wrapper around the singleton call to StorageManager::config()
	 *
	 * Makes it easy to mock the adapter in tests.
	 *
	 * @throws \InvalidArgumentException
	 * @param string $configName
	 * @return array
	 */
	public function storageConfig($configName) {
		return StorageManager::config($configName);
	}

	/**
	 * Wrapper around the singleton call to StorageManager::get()
	 *
	 * Makes it easy to mock the adapter in tests.
	 *
	 * @throws \InvalidArgumentException
	 * @param string $configName
	 * @return array
	 */
	public function getStorageAdapter($configName, $renewObject = false) {
		return StorageManager::get($configName, $renewObject);
	}

	/**
	 * Wrapper around the singleton call to StorageManager::getInstance()
	 *
	 * Makes it easy to mock the adapter in tests.
	 *
	 * @return mixed
	 */
	public function getStorageManager() {
		return StorageManager::getInstance();
	}

}
