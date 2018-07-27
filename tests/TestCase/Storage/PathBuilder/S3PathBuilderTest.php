<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\PathBuilder;

use Burzum\FileStorage\Storage\PathBuilder\S3PathBuilder;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * S3PathBuilderTest
 */
class S3PathBuilderTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.Burzum\FileStorage.FileStorage'
	];

	/**
	 * File Storage Table
	 *
	 * @var \Burzum\FileStorage\Model\Table\FileStorageTable
	 */
	public $FileStorage;

	/**
	 * setUp
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->FileStorage = TableRegistry::getTableLocator()->get('Burzum/FileStorage.FileStorage');
		$this->entity = $this->FileStorage->newEntity([
			'id' => 'file-storage-1',
			'user_id' => 'user-1',
			'foreign_key' => 'item-1',
			'model' => 'Item',
			'filename' => 'cake.icon.png',
			'filesize' => '',
			'mime_type' => 'image/png',
			'extension' => 'png',
			'hash' => '',
			'path' => '',
			'adapter' => 'S3',
		], ['accessibleFields' => ['*' => true]]);
		$this->entity->setAccess('id', true);
	}

	/**
	 * testUrl
	 *
	 * @return void
	 * @todo finish me
	 */
	public function testUrl() {
		$builder = new S3PathBuilder();
		$result = $builder->url($this->entity);
		$this->markTestIncomplete();
		//debug($result);
	}

}
