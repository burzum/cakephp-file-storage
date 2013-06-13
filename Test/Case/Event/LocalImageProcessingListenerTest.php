<?php
App::uses('ImageStorage', 'FileStorage.Model');
App::uses('FileStorageTestCase', 'FileStorage.TestSuite');
/**
 * LocalImageProcessingListener Test
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class LocalImageProcessingListener extends FileStorageTestCase {

	public function setUp() {
		parent::setUp();
		$this->Model = new ImageStorage();
		$this->Listener = new LocalImageProcessingListener();
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->Listener, $this->Model);
		ClassRegistry::flush();
	}

}
