<?php
/**
 * StorageManagerTest
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Test\TestCase\Lib;

use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Lib\StorageManager;;

class StorageManagerTest extends FileStorageTestCase {
/**
 * testAdapter
 *
 * @return void
 */
	public function testAdapter() {
		$result = StorageManager::adapter('Local');
		$this->assertEquals(get_class($result), 'Gaufrette\Filesystem');

		try {
			StorageManager::adapter('Does Not Exist');
			$this->fail('Exception not thrown!');
		} catch (\RuntimeException $e) {}
	}

/**
 * testConfig
 *
 * @return void
 */
	public function testConfig() {
		$result = StorageManager::config('Local');
		$expected = [
			'adapterOptions' => [
				0 => $this->testPath,
				1 => true
			],
			'adapterClass' => '\Gaufrette\Adapter\Local',
			'class' => '\Gaufrette\Filesystem'
		];
		$this->assertEquals($result, $expected);
		$this->assertFalse(StorageManager::config('Does not exist'));
	}

/**
 * testFlush
 *
 * @return void
 */
	public function testFlush() {
		$config = StorageManager::config('Local');
		$result  = StorageManager::flush('Local');
		$this->assertTrue($result);
		$result  = StorageManager::flush('Does not exist');
		$this->assertFalse($result);
		StorageManager::config('Local', $config);
	}
}
