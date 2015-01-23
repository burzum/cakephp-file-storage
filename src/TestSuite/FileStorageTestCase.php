<?php
namespace Burzum\FileStorage\TestSuite;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use Burzum\FileStorage\Lib\StorageManager;
use Burzum\FileStorage\Lib\FileStorageUtils;

/**
 * FileStorageTestCase
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class FileStorageTestCase extends TestCase {

/**
 * Setup test folders and files
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->testPath = TMP . 'file-storage-test' . DS;
		Configure::write('FileStorage.basePath', $this->testPath);

		if (!is_dir($this->testPath)) {
			$Folder = new Folder($this->testPath, true);
		}

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
			'adapterOptions' => array($this->testPath, true),
			'adapterClass' => '\Gaufrette\Adapter\Local',
			'class' => '\Gaufrette\Filesystem'
		));
	}

/**
 * Cleanup test files
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		$Folder = new Folder(TMP . 'file-storage-test');
		$Folder->delete();
	}

}
