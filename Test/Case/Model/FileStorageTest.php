<?php
App::uses('FileStorage', 'FileStorage.Model');
/**
 * File Storage Test
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class FileStorageTest extends CakeTestCase {

/**
 * startTest
 *
 * @return void
 */
	public function startTest($method) {
		parent::startTest($method);
		$this->FileStorage = new FileStorage();
	}

/**
 * endTest
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->FileStorage);
		ClassRegistry::flush();
	}

/**
 * testObject
 *
 * @return void
 */
	public function testObject() {
		$this->assertTrue(is_a($this->FileStorage, 'FileStorage'));
	}

}