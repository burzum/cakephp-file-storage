<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\Listener;

use Cake\Event\Event;
use Cake\Core\Plugin;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class LegacyLocalFileStorageListenerTest extends TestCase {

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

		$this->listener = $this->getMockBuilder('Burzum\FileStorage\Storage\Listener\LegacyLocalFileStorageListener')
			->setMethods([
				'storageAdapter'
			])
			->setConstructorArgs([
				[
					'models' => ['Item']
				]
			])
			->getMock();

		$this->adapterMock = $this->getMock('\Gaufrette\Adapter\Local', [], ['']);

		$this->FileStorage = TableRegistry::get('Burzum/FileStorage.FileStorage');
	}

	/**
	 * Testing that the path is the same as in the old LocalFileStorageListener class.
	 *
	 * @return void
	 */
	public function testPath() {
		$entity = $this->FileStorage->get('file-storage-1');
		$result = $this->listener->pathBuilder()->path($entity);
		$expected = 'files' . DS . '00' . DS . '14' . DS . '90' . DS . 'filestorage1' . DS;
		$this->assertEquals($result, $expected);
	}

	/**
	 * testAfterSave
	 *
	 * @return void
	 */
	public function testAfterSave() {
		$entity = $this->FileStorage->newEntity([
			'model' => 'Item',
			'adapter' => 'Local',
			'id' => '06c0e8e2-4424-11e5-a151-feff819cdc9f',
			'filename' => 'titus.jpg',
			'extension' => 'jpg',
			'mime_type' => 'image/jpeg',
			'file' => [
				'error' => UPLOAD_ERR_OK,
				'tmp_name' => $this->fileFixtures . 'titus.jpg'
			]
		], ['accessibleFields' => ['*' => true]]);

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
		$this->assertEquals($entity->path, 'files' . DS . '05' . DS . '17' . DS . '68' . DS . '06c0e8e2442411e5a151feff819cdc9f' . DS);
	}
}
