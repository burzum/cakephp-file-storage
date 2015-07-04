<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\PathBuilder;

use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Storage\Listener\AbstractListener;
use Burzum\FileStorage\Storage\Listener\ImageProcessingTrait;
use Cake\Core\InstanceConfigTrait;
use \Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class TraitTestClass extends AbstractListener {
	use ImageProcessingTrait;
	public function implementedEvents() {
		return [];
	}
}

class ImageProcessingTraitTest extends FileStorageTestCase {

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

		Configure::write('FileStorage.imageSizes', [
			'Item' => [
				't100' => [
					'thumbnail' => [
						'width' => 300,
						'height' => 300
					]
				],
				'crop50' => [
					'squareCenterCrop' => [
						'size' => 300,
					]
				]
			]
		]);
	}

/**
 * testCreateImageVersions
 *
 * @return void
 */
	public function testCreateImageVersions() {
		$entity = $this->FileStorage->get('file-storage-1');
		$entity->isNew(true);
		$entity->file = [
			'tmp_name' => $this->fileFixtures . 'titus.jpg',
		];

		$builder = new TraitTestClass();
		$builder->pathBuilder('LocalPath');
		$builder->imageProcessor();
		$result = $builder->createImageVersions($entity);
		//debug($result);

		$result = $builder->removeImageVersions($entity, ['crop50']);
		debug($result);
	}
}
