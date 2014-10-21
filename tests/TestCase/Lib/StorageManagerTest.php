<?php
/**
 * StorageManagerTest
 *
 * @author Florian Kr�mer
 * @copyright 2012 - 2014 Florian Kr�mer
 * @license MIT
 */
namespace FileStorage\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;

class StorageManagerTest extends TestCase {
/**
 * testAdapter
 *
 * @todo more tests
 * @return void
 */
	public function testAdapter() {
		$result = StorageManager::adapter('Local');
		$this->assertEqual(get_class($result), 'Gaufrette\Filesystem');

		$result = StorageManager::activeAdapter();
		$this->assertEqual($result, 'Local');

		$result = StorageManager::activeAdapter('invalid-adapter');
		$this->assertFalse($result);

		$result = StorageManager::config();
	}
}