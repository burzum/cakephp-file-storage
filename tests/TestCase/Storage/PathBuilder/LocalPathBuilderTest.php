<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\PathBuilder;

use Burzum\FileStorage\Storage\PathBuilder\LocalPathBuilder;
use Cake\Core\InstanceConfigTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class LocalPathBuilderTest extends TestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Burzum\FileStorage.FileStorage'
	);

	public function setUp() {
		parent::setUp();
		$this->FileStorage = TableRegistry::get('Burzum/FileStorage.FileStorage');
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
			'adapter' => 'Local',
		]);
		$this->entity->accessible('id', true);
	}

/**
 * testPathbuilding
 *
 * @return void
 */
	public function testPathbuilding() {
		$builder = new LocalPathBuilder();

		$result = $builder->fullPath($this->entity);
		$this->assertEquals($result, TMP . 'file-storage-test' . DS . 'files' . DS . '00' . DS . '14' . DS . '90' . DS . 'filestorage1' . DS . 'filestorage1.png');
	}
}
