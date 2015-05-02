<?php
namespace Burzum\FileStorage\Test\TestCase\Event;

use Cake\Event\Event;
use Cake\Filesystem\Folder;
use Cake\ORM\TableRegistry;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Event\LocalFileStorageListener;
use Burzum\FileStorage\Model\Table\FileStorageTable;

/**
 * LocalImageProcessingListener Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 *
 * @property ImageProcessingListener $Listener
 */
class LocalFileStorageListenerTest extends FileStorageTestCase {

/**
 * setUp
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->FileStorage = TableRegistry::get('Burzum/FileStorage.FileStorage');
		$this->Listener = new LocalFileStorageListener();
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Listener, $this->Model);
		TableRegistry::clear();
	}

/**
 * testImplementedEvents
 *
 * @return void
 */
	public function testImplementedEvents() {
		$expected = [
			'FileStorage.afterSave' => [
				'callable' => 'afterSave',
				'priority' => 50,
			],
			'FileStorage.afterDelete' => [
				'callable' => 'afterDelete',
				'priority' => 50
			]
		];
		$result = $this->Listener->implementedEvents();
		$this->assertEquals($result, $expected);
	}

/**
 * testBuildPath
 *
 * @return void
 */
	public function testBuildPath() {
		$entity = $this->FileStorage->get('file-storage-1');
		$result = $this->Listener->buildPath($this->FileStorage, $entity);
		$this->assertEquals($result, 'files' . DS . '00' . DS . '14' . DS . '90' . DS . 'filestorage1' . DS);
	}

/**
 * testAfterDelete
 *
 * @return void
 */
	public function testAfterDelete() {
		$path = $this->testPath . 'test-after-delete-folder';
		$event = new Event('FileStorage.afterDelete',  $this->FileStorage, [
			'record' => [
				'path' => $path,
				'adapter' => 'Local'
			],
		]);
		$folder = new Folder($this->testPath, true);
		$this->Listener->afterDelete($event);
		$this->assertFalse(is_dir($path));
	}

/**
 * testAfterSave
 *
 * @return void
 */
	public function testAfterSave() {
		$entity = $this->FileStorage->get('file-storage-1');
		$entity->isNew(true);
		$entity->file = [
			'tmp_name' => $this->fileFixtures . 'titus.jpg',
		];
		$event = new Event('FileStorage.afterDelete',  $this->FileStorage, [
			'record' => $entity,
		]);
		$this->Listener->afterSave($event);
		$entity = $this->FileStorage->get('file-storage-1');
		$this->assertEquals($entity->path, 'files' . DS . '00' . DS . '14' . DS . '90' . DS . 'filestorage1' . DS);
	}
}
