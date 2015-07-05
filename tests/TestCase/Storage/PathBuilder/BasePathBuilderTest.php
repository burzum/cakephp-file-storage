<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\PathBuilder;

use Burzum\FileStorage\Storage\PathBuilder\BasePathBuilder;
use Cake\Core\InstanceConfigTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class BasePathBuilderTest extends TestCase {

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
		$builder = new BasePathBuilder();
		$config = $builder->config();

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

		$builder->config($config);
		$builder->config('pathSuffix', 'files');
		$result = $builder->path($this->entity);
		$this->assertEquals($result, '00' . DS . '14' . DS . '90' . DS . 'filestorage1' . DS . 'files' . DS);

		$result = $builder->url($this->entity);
		$expected = '00/14/90/filestorage1/files/filestorage1.png';
		$this->assertEquals($result, $expected);
	}

/**
 * testRandomPath
 *
 * @return void
 */
	public function testRandomPath() {
		$builder = new BasePathBuilder();
		$result = $builder->randomPath('test', 5, 'sha1');
		$this->assertEquals($result, '4a\8f\e5\cc\b1\\');

		$result = $builder->randomPath('test', 3, 'sha1');
		$this->assertEquals($result, '4a\8f\e5\\');
	}

/**
 * testEnsureSlash
 *
 * @return void
 */
	public function testEnsureSlash() {
		$string = 'foo/bar';
		$builder = new BasePathBuilder();
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
		$builder = new BasePathBuilder();
		$builder->ensureSlash($string, 'INVALID!');
	}

/**
 * testSplitFilename
 *
 * @return void
 */
	public function testSplitFilename() {
		$builder = new BasePathBuilder();
		$result = $builder->splitFilename('some.fancy.name.jpg');
		$expected = [
			'filename' => 'some.fancy.name',
			'extension' => 'jpg'
		];
		$this->assertEquals($result, $expected);

		$result = $builder->splitFilename('no-extension');
		$expected = [
			'filename' => 'no-extension',
			'extension' => ''
		];
		$this->assertEquals($result, $expected);
	}

/**
 * testStripDashes
 *
 * @return void
 */
	public function testStripDashes() {
		$builder = new BasePathBuilder();
		$result = $builder->stripDashes('with-dashes-!');
		$this->assertEquals($result, 'withdashes!');
	}
}
