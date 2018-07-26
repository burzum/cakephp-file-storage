<?php
namespace Burzum\FileStorage\Test\TestCase\Model\Entity;

use Burzum\FileStorage\Test\TestCase\FileStorageTestCase;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;

/**
 * File Storage Entity Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
class ImageStorageEntityTest extends FileStorageTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.Burzum\FileStorage.FileStorage'
	];

	/**
	 * startTest
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->_removeListeners();
		EventManager::instance()->on($this->listeners['LegacyImageProcessingListener']);

		$this->FileStorage = TableRegistry::getTableLocator()->get('Burzum/FileStorage.FileStorage');
	}

	/**
	 * endTest
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->FileStorage);
		TableRegistry::getTableLocator()->clear();
	}

	/**
	 * testGetPath
	 *
	 * @return void
	 */
	public function testImageVersion() {
		$this->FileStorage->setEntityClass('Burzum/FileStorage.ImageStorage');

		$entity = $this->FileStorage->newEntity([
			'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
			'model' => 'Test',
			'path' => 'test/path/',
			'extension' => 'jpg',
			'adapter' => 'Local'
		], [
			'accessibleFields' => ['*' => true]
		]);

		$result = $entity->imageVersion('t150');
		$this->assertEquals($result, '/test/path/e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg');
	}

}
