<?php
App::uses('FileStorageUtils', 'FileStorage.Utility');
/**
 * 
 */
class FileStorageUtilsTest extends CakeTestCase {
/**
 * testRandomPath
 *
 * @return void
 */
	public function testRandomPath() {
		$result = FileStorageUtils::randomPath('someteststring');
		$this->assertEqual($result, '38\88\98\\');
	}

/**
 * testTrimPath
 *
 * @return void
 */
	public function testTrimPath() {
		$result = FileStorageUtils::trimPath('foobar/');
		$this->assertEqual($result, 'foobar');
	}

}