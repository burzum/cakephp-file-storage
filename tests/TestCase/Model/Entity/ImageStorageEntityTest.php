<?php
namespace Burzum\FileStorage\Test\TestCase\Model\Entity;

use Burzum\FileStorage\Storage\Listener\LocalListener;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;

/**
 * File Storage Entity Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
class ImageStorageEntityTest extends FileStorageTestCase {

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
		$this->ImageStorage = TableRegistry::get('Burzum/FileStorage.ImageStorage');
		$listener = new LocalListener();
		EventManager::instance()->on($listener);
	}

/**
 * endTest
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->ImageStorage);
		TableRegistry::clear();
	}

/**
 * testGetPath
 *
 * @return void
 */
	public function testImageVersion() {
		$entity = $this->ImageStorage->newEntity([
			'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
			'model' => 'Test',
			'path' => 'test/path/',
			'extension' => 'jpg',
			'adapter' => 'Local'
		], ['accessibleFields' => ['*' => true]]);
		$result = $entity->imageVersion('t150');
		$this->assertEquals($result, '/test/path/e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg');
	}
}
