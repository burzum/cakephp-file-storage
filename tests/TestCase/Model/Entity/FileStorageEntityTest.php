<?php
namespace Burzum\FileStorage\Test\TestCase\Model\Entity;

use Burzum\FileStorage\Storage\Listener\LocalListener;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;

/**
 * File Storage Entity Test
 *
 * @author Florian Kr�mer
 * @copyright 2012 - 2016 Florian Kr�mer
 * @license MIT
 */
class FileStorageEntityTest extends FileStorageTestCase {

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
		$listener = new LocalListener();
		EventManager::instance()->on($listener);
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
 * testGetPath
 *
 * @return void
 */
	public function testGetPath() {
		$entity = $this->FileStorage->get('file-storage-1');
		$result = $entity->path();
		$this->assertEquals($result, 'Item' . DS . '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS . 'filestorage1.png');

		$entity = $this->FileStorage->get('file-storage-1');
		$result = $entity->url();
		$this->assertEquals($result, 'Item/14/83/23/filestorage1/filestorage1.png');
	}
}
