<?php
namespace Burzum\FileStorage\Test\TestCase\Event;

use Cake\ORM\TableRegistry;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Model\Table\FileStorageTable;

/**
 * AbstractStorageEventListenerTest Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class AbstractStorageEventListenerTest extends FileStorageTestCase {

/**
 * setUp
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Listener = $this->getMockForAbstractClass('\Burzum\FileStorage\Event\AbstractStorageEventListener');
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Listener);
		TableRegistry::clear();
	}

/**
 * testFsPath
 *
 * @return void
 */
	public function testFsPath() {
		$result = $this->Listener->fsPath('Foobar', 'random-id');
		$this->assertEquals($result, 'Foobar' . DS . '63' . DS . '87' . DS . '12' . DS . 'randomid' . DS);

		$result = $this->Listener->fsPath('Foobar', 'random-id', false);
		$this->assertEquals($result, 'Foobar' . DS . '63' . DS . '87' . DS . '12' . DS);
	}

/**
 * testStripDashes
 *
 * @return void
 */
	public function testStripDashes() {
		$result = $this->Listener->stripDashes('some-string-with-dashes');
		$this->assertEquals($result, 'somestringwithdashes');
	}

/**
 * testGetAdapter
 *
 * @return void
 */
	public function testGetAdapter() {
		$result = $this->Listener->getAdapter('Local');
		$this->assertTrue(is_a($result, '\Gaufrette\Filesystem'));
	}

/**
 * testGetAdapterConfig
 *
 * @return void
 */
	public function testGetAdapterConfig() {
		$result = $this->Listener->getAdapterConfig('Local');
		$this->assertTrue(is_array($result) && !empty($result));
	}

/**
 * testGetAdapterClassName
 *
 * @return void
 */
	public function testGetAdapterClassName() {
		$result = $this->Listener->getAdapterClassName('Local');
		$this->assertFalse($result);
	}

/**
 * testGetAdapterClassName
 *
 * @return void
 */
	public function testCreateTmpFile() {
		$result = $this->Listener->createTmpFile();
		$this->assertTrue(is_string($result));
	}

/**
 * testBuildFileName
 *
 * @return void
 */
	public function testBuildFileName() {
		$table = TableRegistry::get('Burzum/FileStorage.FileStorage');
		$entity = $table->get('file-storage-1');

		$result = $this->Listener->buildFilename($table, $entity);
		$this->assertEquals($result, 'filestorage1.png');

		$this->Listener->config(['preserveExtension' => false]);
		$result = $this->Listener->buildFilename($table, $entity);
		$this->assertEquals($result, 'filestorage1');

		$this->Listener->config(['preserveFilename' => true]);
		$result = $this->Listener->buildFilename($table, $entity);
		$this->assertEquals($result, 'cake.icon.png');
	}

/**
 * testBuildFileName
 *
 * @return void
 */
	public function testBuildPath() {
		$table = TableRegistry::get('Burzum/FileStorage.FileStorage');
		$entity = $table->get('file-storage-1');

		$result = $this->Listener->buildPath($table, $entity);
		$this->assertEquals($result, '00' . DS . '14' . DS . '90' . DS . 'filestorage1' . DS);
	}

}
