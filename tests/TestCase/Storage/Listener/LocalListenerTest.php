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
	}

/**
 * testAfterDelete
 *
 * @return void
 */
	public function testAfterDelete() {

	}
}
