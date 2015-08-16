<?php
namespace Burzum\FileStorage\Test\TestCase\Storage;

use Burzum\FileStorage\Storage\StorageException;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class TestStorageException extends TestCase {

/**
 * testSetAndGetEntity
 *
 * @return void
 */
	public function testSetAndGetEntity() {
		$FileStorage = TableRegistry::get('Burzum/FileStorage.FileStorage');
		$entity = $FileStorage->newEntity([]);
		$exception = new StorageException();
		$exception->setEntity($entity);
		$this->assertEquals($exception->getEntity(), $entity);
	}
}
