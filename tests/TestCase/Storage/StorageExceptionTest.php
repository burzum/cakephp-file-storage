<?php
namespace Burzum\FileStorage\Test\TestCase\Storage;

use Burzum\FileStorage\Storage\StorageException;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Storage Exception Test
 */
class StorageExceptionTest extends TestCase {

	/**
	 * testSetAndGetEntity
	 *
	 * @return void
	 */
	public function testSetAndGetEntity() {
		$FileStorage = TableRegistry::getTableLocator()->get('Burzum/FileStorage.FileStorage');
		$entity = $FileStorage->newEntity([], ['accessibleFields' => ['*' => true]]);
		$exception = new StorageException();
		$exception->setEntity($entity);
		$this->assertEquals($exception->getEntity(), $entity);
	}

}
