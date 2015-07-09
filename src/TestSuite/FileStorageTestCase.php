<?php
namespace Burzum\FileStorage\TestSuite;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use Cake\Core\Plugin;
use Cake\Event\EventManager;
use Burzum\FileStorage\Lib\StorageManager;
use Burzum\FileStorage\Lib\FileStorageUtils;
use Burzum\FileStorage\Event\ImageProcessingListener;
use Burzum\FileStorage\Event\LocalFileStorageListener;

/**
 * FileStorageTestCase
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class FileStorageTestCase extends TestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Burzum\FileStorage.FileStorage'
	);

	public $listeners = [];

/**
 * Setup test folders and files
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->_setupListeners();

		$this->testPath = TMP . 'file-storage-test' . DS;
		$this->fileFixtures = Plugin::path('Burzum/FileStorage') . 'tests' . DS . 'Fixture' . DS . 'File' . DS;

		if (!is_dir($this->testPath)) {
			$Folder = new Folder($this->testPath, true);
		}

		Configure::write('FileStorage.basePath', $this->testPath);
		Configure::write('FileStorage.imageSizes', array(
			'Test' => array(
				't50' => array(
					'thumbnail' => array(
						'mode' => 'outbound',
						'width' => 50, 'height' => 50)),
				't150' => array(
					'thumbnail' => array(
						'mode' => 'outbound',
						'width' => 150, 'height' => 150
					)
				)
			),
			'UserAvatar' => [
				'small' => array(
					'thumbnail' => array(
						'mode' => 'inbound',
						'width' => 80,
						'height' => 80
					)
				)
			]
		));

		FileStorageUtils::generateHashes();

		StorageManager::config('Local', array(
			'adapterOptions' => [$this->testPath, true],
			'adapterClass' => '\Gaufrette\Adapter\Local',
			'class' => '\Gaufrette\Filesystem'
		));

		$this->FileStorage = TableRegistry::get('Burzum/FileStorage.FileStorage');
		$this->ImageStorage = TableRegistry::get('Burzum/FileStorage.ImageStorage');
	}

	protected function _setupListeners() {
		$this->listeners['ImageProcessingListener'] = new ImageProcessingListener();
		$this->listeners['LocalFileStorageListener'] = new LocalFileStorageListener();
		EventManager::instance()->on($this->listeners['ImageProcessingListener']);
		EventManager::instance()->on($this->listeners['LocalFileStorageListener']);
	}

/**
 * Cleanup test files
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();

		$this->_removeListeners();

		TableRegistry::clear();
		$Folder = new Folder(TMP . 'file-storage-test');
		$Folder->delete();
	}

	protected function _removeListeners() {
		foreach ($this->listeners as $listener) {
			EventManager::instance()->off($listener);
		}
	}
}
