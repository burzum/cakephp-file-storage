<?php
App::uses('StorageManager', 'FileStorage.Lib');
/**
 * StorageManagerTest
 *
 * @author Florian Kr�mer
 * @copyright 2012 Florian Kr�mer
 * @license MIT
 */
class StorageManagerTest extends CakeTestCase {
/**
 * testAdapter
 *
 * @todo more tests
 * @return void
 */
	public function testAdapter() {
		$result = StorageManager::adapter('Local');
		$this->assertEquals(get_class($result), 'Gaufrette\Filesystem');

		$result = StorageManager::activeAdapter();
		$this->assertEquals($result, 'Local');

		$result = StorageManager::activeAdapter('invalid-adapter');
		$this->assertFalse($result);

		$result = StorageManager::config();
	}
}