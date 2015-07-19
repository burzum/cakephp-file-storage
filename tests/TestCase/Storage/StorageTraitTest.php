<?php
namespace Burzum\FileStorage\Test\TestCase\Storage;

use Burzum\FileStorage\Storage\StorageTrait;
use Cake\Core\InstanceConfigTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class TestStorageTrait {
	use StorageTrait;
}

class BasePathBuilderTest extends TestCase {

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
		$this->StorageTrait = new TestStorageTrait();
	}

/**
 * testBeforeDelete
 *
 * @return void
 */
	public function testGetStorageAdapter() {
		$result = $this->StorageTrait->storageAdapter('Local');
		$this->assertTrue(is_a($result, '\Gaufrette\Filesystem'));
	}

/**
 * testBeforeDelete
 *
 * @return void
 */
	public function testGetStorageManagerInstance() {
		$result = $this->StorageTrait->storageManager();
		$this->assertTrue(is_a($result, '\Burzum\FileStorage\Storage\StorageManager'));
	}
}
