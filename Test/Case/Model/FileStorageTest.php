<?php
App::uses('FileStorage', 'FileStorage.Model');

class FileStorageTest extends CakeTestCase {

/**
 * startTest
 *
 * @return void
 */
	public function startTest() {
		$this->FileStorage = ClassRegistry::init('FileStorage.FileStorage');
	}

/**
 * endTest
 *
 * @return void
 */
	public function endTest() {
		parent::tearDown();
		unset($this->FileStorage);
		ClassRegistry::flush();
	}

/**
 * 
 */
	public function testStorageAdapter() {
		$this->FileStorage->adapters = array(
			'Local' => array(
				'adapterOptions' => array(),
				'adapterClass' => '\Gaufrette\Adapter\Local',
				'class' => '\Gaufrette\Filesystem'));

		$adapter = $this->FileStorage->storageAdapter('Local');
	}

}