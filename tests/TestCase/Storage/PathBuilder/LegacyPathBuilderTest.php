<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\PathBuilder;

use Burzum\FileStorage\Storage\PathBuilder\LegacyPathBuilder;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class LegacyPathBuilderTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'plugin.Burzum\FileStorage.FileStorage'
	);

	/**
	 * setUp
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->FileStorage = TableRegistry::get('Burzum/FileStorage.FileStorage');
		$this->entity = $this->FileStorage->newEntity([
			'id' => 'file-storage-3',
			'user_id' => 'user-1',
			'foreign_key' => 'item-1',
			'model' => 'Item',
			'filename' => 'titus.jpg',
			'filesize' => '',
			'mime_type' => 'image/jpg',
			'extension' => 'jpg',
			'hash' => '',
			'path' => '',
			'adapter' => 'Local',
			'created' => '2012-01-01 12:00:00',
			'modified' => '2012-01-01 12:00:00',
		], ['accessibleFields' => ['*' => true]]);
		$this->entity->accessible('id', true);
	}

	/**
	 * testLegacyRecord
	 *
	 * @return void
	 */
	public function testLegacyRecord() {
		$builder = new LegacyPathBuilder();
		$result = $builder->path($this->entity);
		$expected = 'files' . DS . '35' . DS . '20' . DS . '80' . DS . 'filestorage3' . DS;
		$this->assertEquals($expected, $result);

		$result = $builder->fullPath($this->entity);
		$expected = 'files' . DS . '35' . DS . '20' . DS . '80' . DS . 'filestorage3' . DS . 'filestorage3.jpg';
		$this->assertEquals($expected, $result);
	}
}
