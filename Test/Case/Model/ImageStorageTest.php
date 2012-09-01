<?php
App::uses('ImageStorage', 'FileStorage.Model');
App::uses('FileStorageTestCase', 'FileStorage.TestSuite');
/**
 * Image Storage Test
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class ImageStorageTest extends FileStorageTestCase {
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
	public function startTest($method) {
		parent::startTest($method);

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
		$this->Image->create();
		$result = $this->Image->save(array(
			'foreign_key' => 'test-1',
			'model' => 'Test',
			'file' => array(
				'name' => 'titus.jpg',
				'size' => 332643,
				'tmp_name' => CakePlugin::path('FileStorage') . DS . 'Test' . DS . 'Fixture' . DS . 'File' . DS . 'titus.jpg',
				'error' => 0)));

		$result = $this->Image->find('first', array(
			'conditions' => array(
				'id' =>  $this->Image->getLastInsertId())));
	}

}