<?php
namespace Burzum\FileStorage\Test\TestCase\Model\Table;

use Burzum\FileStorage\Lib\FileStorageUtils;
use Burzum\FileStorage\Lib\StorageManager;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Filesystem\Folder;
use Cake\ORM\TableRegistry;

use Burzum\FileStorage\TestSuite\FileStorageTestCase;

/**
 * Image Storage Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class ImageStorageTest extends FileStorageTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Burzum\FileStorage.FileStorage'
	);

/**
 * setUp
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Image = TableRegistry::get('Burzum/FileStorage.ImageStorage');
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Image);
		TableRegistry::clear();
	}

/**
 * testProcessVersion
 *
 * @return void
 */
	public function testProcessVersion() {
		$entity = $this->Image->newEntity([
			'foreign_key' => 'test-1',
			'model' => 'Test',
			'file' => [
				'name' => 'titus.jpg',
				'size' => 332643,
				'tmp_name' => Plugin::path('Burzum/FileStorage') . DS . 'tests' . DS . 'Fixture' . DS . 'File' . DS . 'titus.jpg',
				'error' => 0
			]
		]);

		$this->Image->save($entity);
		$result = $this->Image->find()
			->where([
				'id' => $entity->id
			])
			->first();

		$this->assertTrue(!empty($result) && is_a($result, '\Cake\ORM\Entity'));
		$this->assertTrue(file_exists($this->testPath . $result['path']));

		$path = $this->testPath . $result['path'];
		$Folder = new Folder($path);
		$folderResult = $Folder->read();
		$this->assertEquals(count($folderResult[1]), 3);

		Configure::write('FileStorage.imageSizes.Test', array(
			't200' => array(
				'thumbnail' => array(
					'mode' => 'outbound',
					'width' => 200, 'height' => 200
				)
			)
		));
		FileStorageUtils::generateHashes();

		$Event = new Event('ImageVersion.createVersion', $this->Image, array(
			'record' => $result,
			'storage' => StorageManager::adapter('Local'),
			'operations' => array(
				't200' => array(
					'thumbnail' => array(
						'mode' => 'outbound',
						'width' => 200, 'height' => 200
					)
				)
			)
		));

		EventManager::instance()->dispatch($Event);

		$path = $this->testPath . $result['path'];
		$Folder = new Folder($path);
		$folderResult = $Folder->read();
		$this->assertEquals(count($folderResult[1]), 4);


		$Event = new Event('ImageVersion.removeVersion', $this->Image, array(
			'record' => $result,
			'storage' => StorageManager::adapter('Local'),
			'operations' => array(
				't200' => array(
					'thumbnail' => array(
						'mode' => 'outbound',
						'width' => 200, 'height' => 200
					)
				)
			)
		));

		EventManager::instance()->dispatch($Event);

		$path = $this->testPath . $result['path'];
		$Folder = new Folder($path);
		$folderResult = $Folder->read();
		$this->assertEquals(count($folderResult[1]), 3);
	}

/**
 * testGetImageVersions
 *
 * @return void
 */
	public function testGetImageVersions() {
		Configure::write('FileStorage.imageSizes', [
			'Item' => [
				't100' => [
					'thumbnail' => [
						'width' => 300,
						'height' => 300
					]
				],
				'crop50' => [
					'centercrop' => [
						'width' => 300,
						'height' => 300
					]
				]
			]
		]);
		\Burzum\FileStorage\Lib\FileStorageUtils::generateHashes();
		$entity = $this->Image->get('file-storage-2');
		$entity->path = 'images' . DS . '30' . DS . '20' . DS . '10' . DS;
		$result = $this->Image->getImageVersions($entity);
		$expected = [
			't100' => '/images/30/20/10/filestorage2.ead9ceef.jpg',
			'crop50' => '/images/30/20/10/filestorage2.9aade7aa.jpg',
			'original' => '/images/30/20/10/filestorage2.jpg'
		];
		$this->assertEquals($result, $expected);
	}

/**
 * testValidateImageSize
 *
 * @expectedException \InvalidArgumentException
 * @return void
 */
	public function testValidateImageSizeInvalidArgumentException() {
		$file = $this->fileFixtures . 'titus.jpg';
		$this->Image->validateImageSize($file);
	}

/**
 * testValidateImageSize
 *
 * @return void
 */
	public function testValidateImageSize() {
		$file = $this->fileFixtures . 'titus.jpg';
		$result = $this->Image->validateImageSize($file, ['height' => ['>', 100]]);
		$this->assertTrue($result);
		$result = $this->Image->validateImageSize($file, ['height' => ['<', 100]]);
		$this->assertFalse($result);

		$file = [
			'tmp_name' => $file
		];
		$result = $this->Image->validateImageSize($file, ['height' => ['<', 100]]);
		$this->assertFalse($result);
	}
	
/**
 * testDeleteOldFileOnSave
 *
 * @return void
 */
	public function testDeleteOldFileOnSave() {
		$entity = $this->Image->newEntity([
			'foreign_key' => 'test-1',
			'model' => 'Test',
			'file' => [
				'name' => 'titus.jpg',
				'size' => 332643,
				'tmp_name' => Plugin::path('Burzum/FileStorage') . DS . 'tests' . DS . 'Fixture' . DS . 'File' . DS . 'titus.jpg',
				'error' => 0
			]
		]);

		$this->Image->save($entity);
		$result = $this->Image->find()
			->where([
				'id' => $entity->id
			])
			->first();

		$this->assertTrue(!empty($result) && is_a($result, '\Cake\ORM\Entity'));
		$this->assertTrue(file_exists($this->testPath . $result['path']));
		
		$oldImageFile = $this->testPath . $result['path']; 
		$secondEntity = $this->Image->newEntity([
			'foreign_key' => 'test-1',
			'model' => 'Test',
			'file' => [
				'name' => 'titus.jpg',
				'size' => 332643,
				'tmp_name' => Plugin::path('Burzum/FileStorage') . DS . 'tests' . DS . 'Fixture' . DS . 'File' . DS . 'titus.jpg',
				'error' => 0
			],
			'old_file_id' => $entity->id
		]);

		$this->Image->save($secondEntity);
		$result = $this->Image->find()
			->where([
				'id' => $secondEntity->id
			])
			->first();
		
		$this->assertTrue(!empty($result) && is_a($result, '\Cake\ORM\Entity'));
		$this->assertTrue(file_exists($this->testPath . $result['path']));
		
		$Folder = new Folder($oldImageFile);
		$folderResult = $Folder->read(); 
		$this->assertEquals(count($folderResult[1]), 0);
	}
}
