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
			->setMethods(['storageAdapter'])
			->setConstructorArgs([['models' => ['Item']]])
			->getMock();

		$this->adapterMock = $this->getMock('\Gaufrette\Adapter\Local', [], ['']);

		$this->FileStorage = TableRegistry::get('Burzum/FileStorage.FileStorage');
	}

/**
 * Testing that the path is the same as in the old LocalFileStorageListener class.
 */
	public function testPath() {
		$entity = $this->FileStorage->get('file-storage-1');
		$result = $this->listener->pathBuilder()->path($entity);
		$expected = 'files' . DS . '00' . DS . '14' . DS . '90' . DS . 'filestorage1' . DS;
		$this->assertEquals($result, $expected);
	}
}