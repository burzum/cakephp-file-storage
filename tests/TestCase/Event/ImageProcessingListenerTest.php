<?php
namespace Burzum\FileStorage\Test\TestCase\Event;

use Burzum\FileStorage\Storage\StorageManager;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Event\ImageProcessingListener;
use Burzum\FileStorage\Model\Table\FileStorageTable;

class TestImageProcessingListener extends ImageProcessingListener {
	public function buildPath($image, $extension = true, $hash = null) {
		return $this->_buildPath($image, $extension, $hash);
	}
	public function buildAwsS3Path($Event) {
		$this->_buildAwsS3Path($Event);
	}
}

/**
 * LocalImageProcessingListener Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 *
 * @property ImageProcessingListener $Listener
 */
class ImageProcessingListenerTest extends FileStorageTestCase {

/**
 * setUp
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Model = new FileStorageTable();
		$this->Listener = new ImageProcessingListener();
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Listener, $this->Model);
		TableRegistry::clear();
	}

/**
 * testImplementedEvents
 *
 * @return void
 */
	public function testImplementedEvents() {
		$expected = [
			'ImageVersion.createVersion' => 'createVersions',
			'ImageVersion.removeVersion' => 'removeVersions',
			'ImageVersion.getVersions' => 'imagePath',
			'ImageStorage.beforeSave' => 'beforeSave',
			'ImageStorage.afterSave' => 'afterSave',
			'ImageStorage.afterDelete' => 'afterDelete',
			'FileStorage.ImageHelper.imagePath' => 'imagePath' // Deprecated
		];
		$result = $this->Listener->implementedEvents();
		$this->assertEquals($result, $expected);
	}

/**
 * testBuildPath
 *
 * @return void
 */
	public function testBuildPath() {
		$this->Listener = new TestImageProcessingListener(array(
			'preserveFilename' => true,
		));

		$result = $this->Listener->buildPath(array(
			'filename' => 'foobar.jpg',
			'path' => '/xx/xx/xx/uuid/',
			'extension' => 'jpg'
		));
		$this->assertEquals($result, '/xx/xx/xx/uuid/foobar.jpg');

		$result = $this->Listener->buildPath(array(
			'filename' => 'foobar.jpg',
			'path' => '/xx/xx/xx/uuid/',
			'extension' => 'jpg'
		), true, '5gh2hf');
		$this->assertEquals($result, '/xx/xx/xx/uuid/foobar.5gh2hf.jpg');
	}

/**
 * testBuildAwsS3Path
 *
 * @return void
 */
	public function testBuildAwsS3Path() {
		$this->Listener = new TestImageProcessingListener(array(
			'preserveFilename' => true,
		));

		$image = $this->FileStorage->get('file-storage-4');

		StorageManager::config($image->get('adapter'), [
			'adapterOptions' => [null, 'bucket1'],
		]);

		$eventOptions = [
			'hash' => 'abc',
			'image' => $image,
			'version' => 'small',
			'options' => [],
			'pathType' => 'url'
		];

		$event = new Event('FileStorage.ImageHelper.imagePath', $this, $eventOptions);

		$expected = 'http://s3.amazonaws.com/bucket1/titus.abc.jpg';

		$this->Listener->buildAwsS3Path($event);
		$this->assertEquals($expected, $event->data['path']);

		// Make sure it returns same path if called more than once
		$this->Listener->buildAwsS3Path($event);
		$this->assertEquals($expected, $event->data['path']);

		// Make sure it doesn't change path property
		$this->assertNull($image->get('path'));
	}
}
