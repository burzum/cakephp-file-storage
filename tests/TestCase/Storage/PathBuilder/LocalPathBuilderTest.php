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

	public function testPathbuilding() {
		$builder = new LocalPathBuilder();

		$result = $builder->filename($this->entity);
		$this->assertEquals($result, 'filestorage1.png');

		$result = $builder->path($this->entity);
		$this->assertEquals($result, '00' . DS . '14' . DS . '90' . DS . 'filestorage1' . DS);

		$result = $builder->fullPath($this->entity);
		$this->assertEquals($result, '00' . DS . '14' . DS . '90' . DS . 'filestorage1' . DS . 'filestorage1.png');

		$builder->config('pathPrefix', 'files');
		$result = $builder->path($this->entity);
		$this->assertEquals($result, 'files' . DS . '00' . DS . '14' . DS . '90' . DS . 'filestorage1' . DS);

		$builder->config('pathPrefix', 'files');
		$result = $builder->filename($this->entity);
		$this->assertEquals($result, 'filestorage1.png');

		$builder->config('preserveFilename', true);
		$result = $builder->filename($this->entity);
		$this->assertEquals($result, 'cake.icon.png');
	}

/**
 * testEnsureSlash
 *
 * @return void
 */
	public function testEnsureSlash() {
		$string = 'foo/bar';
		$builder = new LocalPathBuilder();
		$result = $builder->ensureSlash($string, 'both');
		$this->assertEquals($result, DS . $string . DS);

		$result = $builder->ensureSlash(DS . $string . DS, 'both');
		$this->assertEquals($result, DS . $string . DS);
	}

/**
 * testEnsureSlashInvalidArgumentException
 *
 * @expectedException \InvalidArgumentException
 */
	public function testEnsureSlashInvalidArgumentException() {
		$string = 'foo/bar';
		$builder = new LocalPathBuilder();
		$builder->ensureSlash($string, 'INVALID!');
	}
}
