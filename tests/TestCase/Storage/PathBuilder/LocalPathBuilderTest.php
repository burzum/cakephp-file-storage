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
			'id' => 'be4e23a0-142f-11e5-b60b-1697f925ec7b',
			'foreign_key' => 'c1a6ed2a-142f-11e5-b60b-1697f925ec7b',
			'model' => 'Photos',
			'filesize' => 252576,
			'filename' => 'somefancyphoto.jpg',
			'extension' => 'png',
			'mime_type' => 'image/jpeg'
		]);
		$this->entity->accessible('id', true);
	}

	public function testWebPath() {
		//debug($this->entity->toArray());
		$builder = new LocalPathBuilder($this->entity);
		$result = $builder->url($this->entity);
		debug($result);
		$result = $builder->fullPath($this->entity);
		debug($result);
	}
}
