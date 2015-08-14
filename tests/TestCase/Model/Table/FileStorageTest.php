<?php
namespace Burzum\FileStorage\Test\TestCase\Model\Table;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;

/**
 * File Storage Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class FileStorageTest extends FileStorageTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Burzum\FileStorage.FileStorage'
	);

/**
 * startTest
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->FileStorage = TableRegistry::get('Burzum/FileStorage.FileStorage');
	}

/**
 * endTest
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->FileStorage);
		TableRegistry::clear();
	}

/**
 * testBeforeDelete
 *
 * @return void
 */
	public function testBeforeDelete() {
		$entity = $this->FileStorage->get('file-storage-1');
		$event = new Event('Model.beforeDelete', $this->FileStorage);
		$this->FileStorage->beforeDelete($event, $entity);
		$this->assertEquals($this->FileStorage->record, $entity);
	}

/**
 * testBeforeDelete
 *
 * @return void
 */
	public function testGetStorageAdapter() {
		$result = $this->FileStorage->getStorageAdapter('Local');
		$this->assertTrue(is_a($result, '\Gaufrette\Filesystem'));
	}

/**
 * testGetEventManager
 *
 * @return void
 */
	public function testGetEventManager() {
		$result = $this->FileStorage->getEventManager();
		$this->assertTrue(is_a($result, '\Cake\Event\EventManager'));
	}

/**
 * testAfterDelete
 *
 * @return void
 */
	public function testAfterDelete() {
		$entity = $this->FileStorage->get('file-storage-1');
		$entity->adapter = 'Local';
		$event = new Event('FileStorage.afterDelete',  $this->FileStorage, [
			'record' => $entity,
			'adapter' => 'Local'
		]);
		$result = $this->FileStorage->afterDelete($event, $entity, []);
		$this->assertTrue($result);
	}

/**
 * testBeforeMarshal
 *
 * @return void
 */
	public function testBeforeMarshal() {
		$filename = \Cake\Core\Plugin::path('Burzum/FileStorage') . DS . 'tests' . DS . 'Fixture' . DS . 'File' . DS . 'titus.jpg';
		$event = new Event('Model.beforeMarshal', $this->FileStorage);

		$data = new \ArrayObject([
			'file' => [
				'name' => 'titus.jpg',
				'tmp_name' => $filename
			]
		]);

		$this->FileStorage->beforeMarshal($event, $data);

		$this->assertEquals(332643, $data['filesize']);
		$this->assertEquals('Local', $data['adapter']);
		$this->assertEquals('image/jpeg', $data['mime_type']);
		$this->assertEquals('jpg', $data['extension']);
		$this->assertEquals('file_storage', $data['model']);
	}
}
