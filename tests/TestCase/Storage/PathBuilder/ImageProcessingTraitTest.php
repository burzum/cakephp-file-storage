<?php
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
	public function __construct(array $config = []) {
		parent::__construct($config);
		$this->_loadImageProcessingFromConfig();
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
		]);
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
 * @todo finish me
 * @return void
 */
	public function testCreateImageVersions() {
		$entity = $this->FileStorage->get('file-storage-3');
		$listener = new TraitTestClass();
		$path = $listener->pathBuilder('LocalPath', ['preserveFilename' => true])->path($entity);

		new Folder($this->testPath . $path, true);
		copy($this->fileFixtures . 'titus.jpg', $this->testPath . $path . 'titus.jpg');

		$listener->imageProcessor();
		$result = $listener->createImageVersions($entity);
		//debug($result);
	}

/**
 * getAllVersionsKeysForModel
 *
 * @return void
 */
	public function testGetAllVersionsKeysForModel() {
		$builder = new TraitTestClass();
		$result = $builder->getAllVersionsKeysForModel('Item');
		$expected = [
			0 => 't100',
			1 => 'crop50'
		];
		$this->assertEquals($result, $expected);
	}

/**
 * testRemoveImageVersions
 *
 * @return void
 */
	public function testRemoveImageVersions() {
//		$this->Listener->expects($this->at(0))
//			->method('getAdapter')
//			->with(['Local'])
//			->will($this->returnValue(true));
//
//		$result = $this->Listener->removeImageVersions($this->entity, ['t100', 'crop50']);
//		debug($result);
	}
}
