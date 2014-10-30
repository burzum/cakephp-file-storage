<?php
namespace Burzum\FileStorage\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Burzum\FileStorage\Model\Table\FileStorageTable;

/**
 * File Storage Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2014 Florian Krämer
 * @license MIT
 */
class FileStorageTest extends TestCase {

/**
 * startTest
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->FileStorage = new FileStorageTable();
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
 * testObject
 *
 * @return void
 */
	public function testObject() {
		$this->assertTrue(is_a($this->FileStorage, 'FileStorage'));
	}

/**
 * testFsPath
 *
 * @return void
 */
	public function testFsPath() {
		$result = $this->FileStorage->fsPath('Foobar', 'random-id');
		$this->assertEquals($result, 'Foobar' . DS . '63' . DS . '87' . DS . '12' . DS . 'randomid' . DS);

		$result = $this->FileStorage->fsPath('Foobar', 'random-id', false);
		$this->assertEquals($result, 'Foobar' . DS . '63' . DS . '87' . DS . '12' . DS);
	}

/**
 * testStripUuid
 *
 * @return void
 */
	public function testStripUuid() {
		$result = $this->FileStorage->stripUuid('some-string-with-dashes');
		$this->assertEquals($result, 'somestringwithdashes');
	}

}
