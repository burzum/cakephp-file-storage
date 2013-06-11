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
	public function setUp() {
		parent::setUp();
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

/**
 * testFsPath
 *
 * @return void
 */
	public function testFsPath() {
		$result = $this->FileStorage->fsPath('Foobar', 'random-id');
		$this->assertEqual($result, 'Foobar\63\87\12\randomid\\');

		$result = $this->FileStorage->fsPath('Foobar', 'random-id', false);
		$this->assertEqual($result, 'Foobar\63\87\12\\');
	}

/**
 * testStripUuid
 *
 * @return void
 */
	public function testStripUuid() {
		$result = $this->FileStorage->stripUuid('some-string-with-dashes');
		$this->assertEqual($result, 'somestringwithdashes');
	}

}