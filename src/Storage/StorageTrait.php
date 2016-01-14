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
		if (empty($configName) || !is_string($configName)) {
			throw new \InvalidArgumentException('First arg must be a non empty string!');
		}
		return StorageManager::config($configName);
	}

	/**
	 * Wrapper around the singleton call to StorageManager::adapter()
	 *
	 * Makes it easy to mock the adapter in tests.
	 *
	 * @throws \InvalidArgumentException
	 * @param string $configName
	 * @return array
	 */
	public function storageAdapter($configName, $renewObject = false) {
		if (empty($configName) || !is_string($configName)) {
			throw new \InvalidArgumentException('First arg must be a non empty string!');
		}
		return StorageManager::adapter($configName, $renewObject);
	}

	/**
	 * Wrapper around the singleton call to StorageManager::getInstance()
	 *
	 * Makes it easy to mock the adapter in tests.
	 *
	 * @return mixed
	 */
	public function storageManager() {
		return StorageManager::getInstance();
	}
}
