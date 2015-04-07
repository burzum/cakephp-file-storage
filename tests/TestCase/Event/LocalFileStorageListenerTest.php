<?php
namespace Burzum\FileStorage\Test\TestCase\Event;

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
}
