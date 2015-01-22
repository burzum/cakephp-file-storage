<?php
namespace Burzum\FileStorage\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Model\Table\FileStorageTable;

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
 * testObject
 *
 * @return void
 */
	public function testObject() {
		$this->assertTrue(is_a($this->FileStorage, 'Burzum\FileStorage\Model\Table\FileStorageTable'));
	}

}
