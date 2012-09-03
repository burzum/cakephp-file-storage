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
 * setUp
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Image = new ImageStorage();
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Image);
		ClassRegistry::flush();
	}

/**
 * testProcessVersion
 *
 * @return void
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
				'id' => $this->Image->getLastInsertId())));

		$this->assertTrue(!empty($result) && is_array($result));
		$this->assertTrue(file_exists($this->testPath . $result['FileStorage']['path']));

		$Folder = new Folder(dirname($this->testPath . $result['FileStorage']['path']));
		$result = $Folder->read();
		$this->assertEqual(count($result[1]), 3);
	}

}