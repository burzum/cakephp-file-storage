<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Test\TestCase\Storage\PathBuilder;

use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Storage\Listener\AbstractListener;
use Burzum\FileStorage\Storage\Listener\ImageProcessingTrait;
use Cake\Core\InstanceConfigTrait;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Filesystem\Folder;

class TraitTestClass extends AbstractListener {
	use ImageProcessingTrait;
	public $_defaultConfig = [
		'pathBuilder' => 'Base',
		'pathBuilderOptions' => [
			'preserveFilename' => true
		]
	];
	public function __construct(array $config = []) {
		parent::__construct($config);
		$this->_loadImageProcessingFromConfig();
	}
	public function checkImageVersions($identifier, array $versions) {
		return $this->_checkImageVersions($identifier, $versions);
	}
	public function implementedEvents() {
		return [];
	}
}

class ImageProcessingTraitTest extends FileStorageTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Burzum\FileStorage.FileStorage'
	);

	public function setUp() {
		parent::setUp();
		$this->FileStorage = TableRegistry::get('Burzum/FileStorage.FileStorage');
		$this->entity = $this->FileStorage->newEntity([
			'id' => 'file-storage-1',
			'user_id' => 'user-1',
			'foreign_key' => 'item-1',
			'model' => 'Item',
			'filename' => 'cake.icon.png',
			'filesize' => '',
			'mime_type' => 'image/png',
			'extension' => 'png',
			'hash' => '',
			'path' => '',
			'adapter' => 'Local',
		], ['accessibleFields' => ['*' => true]]);
		$this->entity->accessible('id', true);

		Configure::write('FileStorage.imageSizes', [
			'Item' => [
				't100' => [
					'thumbnail' => [
						'width' => 200,
						'height' => 200
					]
				],
				'crop50' => [
					'squareCenterCrop' => [
						'size' => 300,
					]
				]
			]
		]);

		$this->Listener = $this->getMockBuilder('TraitTestClass')
			->setMethods([
				'getAdapter'
			])
			->getMock();
	}

/**
 * testCreateImageVersions
 *
 * @return void
 */
	public function testCreateImageVersions() {
		$entity = $this->FileStorage->get('file-storage-3');
		$listener = new TraitTestClass();
		$path = $listener->pathBuilder()->path($entity);

		new Folder($this->testPath . $path, true);
		copy($this->fileFixtures . 'titus.jpg', $this->testPath . $path . 'titus.jpg');

		$listener->imageProcessor();
		$result = $listener->createImageVersions($entity, ['t100', 'crop50']);
		$expected = [
			't100' => [
				'status' => 'success',
				'path' => '95' . DS . '61' . DS . '80' . DS . 'filestorage3' . DS . 'titus.20876bcd.jpg',
				'hash' => '20876bcd'
			],
			'crop50' => [
				'status' => 'success',
				'path' => '95' . DS . '61' . DS . '80' . DS . 'filestorage3' . DS . 'titus.41e51a3f.jpg',
				'hash' => '41e51a3f'
			]
		];
		$this->assertEquals($expected, $result);
		$this->assertFileExists($this->testPath . '95' . DS . '61' . DS . '80' . DS . 'filestorage3' . DS . 'titus.20876bcd.jpg');
		$this->assertFileExists($this->testPath . '95' . DS . '61' . DS . '80' . DS . 'filestorage3' . DS . 'titus.41e51a3f.jpg');

		$result = $listener->removeImageVersions($entity, ['t100']);
		$expected = [
			't100' => [
				'status' => 'success',
				'hash' => '20876bcd',
				'path' => '95' . DS . '61' . DS . '80' . DS . 'filestorage3' . DS . 'titus.20876bcd.jpg'
			]
		];
		$this->assertEquals($result, $expected);
		$this->assertFileNotExists($this->testPath . '95' . DS . '61' . DS . '80' . DS . 'filestorage3' . DS . 'titus.20876bcd.jpg');
		$this->assertFileExists($this->testPath . '95' . DS . '61' . DS . '80' . DS . 'filestorage3' . DS . 'titus.41e51a3f.jpg');
	}

/**
 * testCheckImageVersionsRuntimeExceptionIdentifier
 *
 * @expectedException \RuntimeException
 */
	public function testCheckImageVersionsRuntimeExceptionIdentifier() {
		$listener = new TraitTestClass();
		$listener->checkImageVersions('does not exist', []);
	}

/**
 * testCheckImageVersionsRuntimeExceptionVersion
 *
 * @expectedException \RuntimeException
 */
	public function testCheckImageVersionsRuntimeExceptionVersion() {
		$listener = new TraitTestClass();
		$listener->checkImageVersions('Item', ['does not exist!']);
	}

/**
 * getAllVersionsKeysForModel
 *
 * @return void
 */
	public function testGetAllVersionsKeysForModel() {
		$listener = new TraitTestClass();
		$result = $listener->getAllVersionsKeysForModel('Item');
		$expected = [
			0 => 't100',
			1 => 'crop50'
		];
		$this->assertEquals($result, $expected);
	}
}
