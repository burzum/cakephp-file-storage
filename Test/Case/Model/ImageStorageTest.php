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

		$path = $this->testPath . $result['FileStorage']['path'];
		$Folder = new Folder($path);
		$folderResult = $Folder->read();
		$this->assertEqual(count($folderResult[1]), 3);

		Configure::write('Media.imageSizes.Test', array(
			't200' => array(
				'thumbnail' => array(
					'mode' => 'outbound',
					'width' => 200, 'height' => 200))));
		ClassRegistry::init('FileStorage.ImageStorage')->generateHashes();

		$Event = new CakeEvent('ImageVersion.createVersion', $this->Image, array(
			'record' => $result,
			'storage' => StorageManager::adapter('Local'),
			'operations' => array(
				't200' => array(
					'thumbnail' => array(
						'mode' => 'outbound',
						'width' => 200, 'height' => 200)))));

		CakeEventManager::instance()->dispatch($Event);

		$path = $this->testPath . $result['FileStorage']['path'];
		$Folder = new Folder($path);
		$folderResult = $Folder->read();
		$this->assertEqual(count($folderResult[1]), 4);
	}

}