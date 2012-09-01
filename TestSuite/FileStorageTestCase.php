<?php
App::uses('CakeTestCase', 'TestSuite');
/**
 * FileStorageTestCase
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class FileStorageTestCase extends CakeTestCase {

	public function startTest($method) {
		parent::startTest($method);

		Configure::write('Media.basePath', TMP . 'file-storage-test' . DS);
		if (!is_dir(TMP . 'file-storage-test')) {
			mkdir(TMP . 'file-storage-test');
		}

		Configure::write('Media.imageSizes', array(
			'Test' => array(
				't50' => array(
					'thumbnail' => array(
						'mode' => 'outbound',
						'width' => 50, 'height' => 50)),
				't150' => array(
					'thumbnail' => array(
						'mode' => 'outbound',
						'width' => 150, 'height' => 150)))));

		ClassRegistry::init('FileStorage.ImageStorage')->generateHashes();

		StorageManager::config('Local', array(
			'adapterOptions' => array(Configure::read('Media.basePath'), true),
			'adapterClass' => '\Gaufrette\Adapter\Local',
			'class' => '\Gaufrette\Filesystem'));
	}

	public function endTest() {
		$Folder = new Folder();
		$Folder->delete(TMP . 'file-storage-test');
	}

}