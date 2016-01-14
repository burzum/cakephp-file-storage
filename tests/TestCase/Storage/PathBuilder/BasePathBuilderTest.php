<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\PathBuilder;

use Burzum\FileStorage\Storage\PathBuilder\BasePathBuilder;
use Cake\Core\InstanceConfigTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class TestBasePathBuilder extends BasePathBuilder {

	/**
	 *
	 */
	public function randomPathTestMethod($string) {
		return $string . 'test';
	}
}

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
		], ['accessibleFields' => ['*' => true]]);
		$this->entity->accessible('id', true);
	}

	/**
	 * testPathbuilding
	 *
	 * @return void
	 */
	public function testPathbuilding() {
		$builder = new BasePathBuilder();
		$config = $builder->config();

		$result = $builder->filename($this->entity);
		$this->assertEquals($result, 'filestorage1.png');

		$result = $builder->path($this->entity);
		$this->assertEquals($result, '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS);

		$result = $builder->fullPath($this->entity);
		$this->assertEquals($result, '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS . 'filestorage1.png');

		$builder->config('pathPrefix', 'files');
		$result = $builder->path($this->entity);
		$this->assertEquals($result, 'files' . DS . '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS);
		$result = $builder->path($this->entity, ['pathPrefix' => 'images']);
		$this->assertEquals($result, 'images' . DS . '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS);

		$builder->config('pathPrefix', 'files');
		$result = $builder->filename($this->entity);
		$this->assertEquals($result, 'filestorage1.png');

		$builder->config('preserveFilename', true);
		$result = $builder->filename($this->entity);
		$this->assertEquals($result, 'cake.icon.png');

		$builder->config($config);
		$builder->config('pathSuffix', 'files');
		$result = $builder->path($this->entity);
		$this->assertEquals($result, '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS . 'files' . DS);

		$builder->config($config);
		$builder->config('pathPrefix', 'files');
		$result = $builder->path($this->entity);
		$this->assertEquals($result, 'files' . DS . '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS);

		$builder->config($config);
		$result = $builder->url($this->entity);
		$expected = '14/83/23/filestorage1/filestorage1.png';
		$this->assertEquals($result, $expected);
	}

	/**
	 * testRandomPath
	 *
	 * @return void
	 */
	public function testRandomPath() {
		$builder = new TestBasePathBuilder();
		$result = $builder->randomPath('test', 5, 'sha1');
		$this->assertEquals($result, '4a' . DS . '8f' . DS . 'e5' . DS . 'cc' . DS . 'b1' . DS);

		$result = $builder->randomPath('test', 3, 'sha1');
		$this->assertEquals($result, '4a' . DS . '8f' . DS . 'e5' . DS);

		if (PHP_INT_SIZE === 4) {
			$result = $builder->randomPath('test', 3, 'crc32');
			$this->assertEquals($result, '00' . DS . '33' . DS . '73' . DS);
		}

		if (PHP_INT_SIZE === 8) {
			$result = $builder->randomPath('test', 3, 'crc32');
			$this->assertEquals($result, '96' . DS . '39' . DS . '23' . DS);
		}

		$result = $builder->randomPath('test', 3, 'randomPathTestMethod');
		$this->assertEquals($result, 'testtest');
	}

	/**
	 * testRandomPathInvalidArgumentException
	 *
	 * @expectedException \InvalidArgumentException
	 * @return void
	 */
	public function testRandomPathInvalidArgumentException() {
		$builder = new BasePathBuilder();
		$result = $builder->randomPath('test', 5, 'does-not-exist');
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
