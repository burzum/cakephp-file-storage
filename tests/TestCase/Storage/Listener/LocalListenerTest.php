<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\Listener;

use Burzum\FileStorage\Storage\Listener\LocalListener;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Cake\Event\Event;
use Cake\Core\InstanceConfigTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class LocalListenerTest extends FileStorageTestCase {

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
		$this->listener = $this->getMockBuilder('Burzum\FileStorage\Storage\Listener\LocalListener')
			->setMethods(['getAdapter'])
			->getMock();

		$this->adapterMock = $this->getMock('\Gaufrette\Adapter\Local', [], ['']);

		$this->FileStorage = TableRegistry::get('Burzum/FileStorage.FileStorage');
	}

/**
 * testAfterSave
 *
 * @todo finish me
 * @return void
 */
	public function testAfterSave() {
		$entity = $this->FileStorage->get('file-storage-3');
		$entity->isNew(true);
		$entity->file = ['tmp_name' => $this->fileFixtures . 'titus.jpg'];
		$event = new Event('FileStorage.afterSave', $this->FileStorage, [
			'record' => $entity
		]);

		$this->listener->expects($this->at(0))
			->method('getAdapter')
			->will($this->returnValue($this->adapterMock));

		$this->listener->afterSave($event);
	}

/**
 * testAfterDelete
 *
 * @return void
 */
	public function testAfterDelete() {

	}
}
