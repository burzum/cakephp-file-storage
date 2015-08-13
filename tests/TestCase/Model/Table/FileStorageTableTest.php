<?php
namespace Burzum\FileStorage\Test\TestCase\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;

/**
 * File Storage Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class FileStorageTableTest extends FileStorageTestCase {

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
		unset($this->FileStorageBehavior);
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
}
