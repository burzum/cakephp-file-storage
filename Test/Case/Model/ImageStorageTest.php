<?php
App::uses('ImageStorage', 'FileStorage.Model');
/**
 * Image Storage Test
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class ImageStorageTest extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.FileStorage.FileStorage');

/**
 * startTest
 *
 * @return void
 */
	public function startTest() {
		$this->Image = new ImageStorage();
	}

/**
 * endTest
 *
 * @return void
 */
	public function endTest() {
		parent::tearDown();
		unset($this->Image);
		ClassRegistry::flush();
	}

/**
 * 
 */
	public function testProcessVersion() {

	}

}