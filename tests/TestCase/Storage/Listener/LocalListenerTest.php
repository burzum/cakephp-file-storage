<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\Listener;

use Cake\Event\Event;
use Cake\Core\Plugin;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class LocalListenerTest extends TestCase {

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

		Configure::write('FileStorage.imageSizes', []);
		$this->fileFixtures = Plugin::path('Burzum/FileStorage') . 'tests' . DS . 'Fixture' . DS . 'File' . DS;

		$this->listener = $this->getMockBuilder('Burzum\FileStorage\Storage\Listener\LocalListener')
			->setMethods(['storageAdapter'])
			->setConstructorArgs([['models' => ['Item']]])
			->getMock();

		$this->adapterMock = $this->getMock('\Gaufrette\Adapter\Local', [], ['']);

		$this->FileStorage = TableRegistry::get('Burzum/FileStorage.FileStorage');
	}

	/**
	 * testimplementedEvents
	 *
	 * @return void
	 */
	public function testimplementedEvents() {
		$expected = [
			'FileStorage.path' => 'getPath',
			'FileStorage.afterSave' => 'afterSave',
			'FileStorage.afterDelete' => 'afterDelete',
			'ImageStorage.afterSave' => 'afterSave',
			'ImageStorage.afterDelete' => 'afterDelete',
			'ImageVersion.removeVersion' => 'removeImageVersion',
			'ImageVersion.createVersion' => 'createImageVersion',
			'ImageVersion.getVersions' => 'imagePath',
			'FileStorage.ImageHelper.imagePath' => 'imagePath',
			'FileStorage.getPath' => 'getPath'
		];
		$result = $this->listener->implementedEvents();
		$this->assertEquals($result, $expected);
	}

	/**
	 * testImagePath
	 *
	 * @return void
	 */
	public function testImagePath() {
		Configure::write('FileStorage.imageSizes', [
			'Test' => [
				't150' => [
					'thumbnail' => [
						'mode' => 'outbound',
						'width' => 150, 'height' => 150
					]
				]
			],
		]);
		$image = $this->FileStorage->newEntity([
			'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
			'model' => 'Test',
			'path' => 'test/path/',
			'extension' => 'jpg',
			'adapter' => 'Local'
		], ['accessibleFields' => ['*' => true]]);

		$event = new Event('ImageVersion.getVersions', $this->FileStorage, [
			'image' => $image,
			'version' => 't150'
		]);

		$expected = 'Test' . DS . '5c' . DS . '39' . DS . '33' . DS . 'e479b480f60b11e1a21f0800200c9a66' . DS . 'e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg';
		$this->listener->imagePath($event);
		$this->assertEquals($event->data['path'], $expected);
		$this->assertEquals($event->result, $expected);
	}

	/**
	 * testAfterSave
	 *
	 * @return void
	 */
	public function testAfterSave() {
		$entity = $this->FileStorage->get('file-storage-3');
		$entity->isNew(true);
		$entity->file = [
			'name' => 'titus.jpg',
			'tmp_name' => $this->fileFixtures . 'titus.jpg'
		];
		$event = new Event('FileStorage.afterSave', $this->FileStorage, [
			'record' => $entity,
			'table' => $this->FileStorage
		]);

		$this->listener->expects($this->at(0))
			->method('storageAdapter')
			->will($this->returnValue($this->adapterMock));

		$this->adapterMock->expects($this->at(0))
			->method('write')
			->will($this->returnValue(true));

		$this->listener->afterSave($event, $entity);
		$this->assertTrue($event->result);
	}

	/**
	 * testAfterDelete
	 *
	 * @return void
	 */
	public function testAfterDelete() {
		$entity = $this->FileStorage->get('file-storage-3');
		$event = new Event('FileStorage.afterDelete', $this->FileStorage, [
			'record' => $entity,
			'entity' => $entity,
			'table' => $this->FileStorage
		]);

		$this->listener->expects($this->at(0))
			->method('storageAdapter')
			->will($this->returnValue($this->adapterMock));

		$this->adapterMock->expects($this->at(0))
			->method('delete')
			->will($this->returnValue(true));

		$this->listener->afterDelete($event, $entity);
		$this->assertTrue($event->result);
	}
}
