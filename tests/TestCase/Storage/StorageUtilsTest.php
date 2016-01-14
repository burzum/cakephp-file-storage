<?php
namespace Burzum\FileStorage\Test\TestCase\Lib\Utility;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Storage\StorageUtils;

/**
 * Storage Utils Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
class StorageUtilsTest extends FileStorageTestCase {

	public function setUp() {
		parent::setUp();
		$this->fileFixtures = Plugin::path('Burzum/FileStorage') . 'tests' . DS . 'Fixture' . DS . 'File' . DS;
	}

/**
 * testRandomPath
 *
 * @return void
 */
	public function testRandomPath() {
		$this->skipIf(PHP_INT_SIZE === 8);

		$result = StorageUtils::randomPath('someteststring');
		$this->assertEquals($result, '38' . DS . '88' . DS . '98' . DS);

		$result = StorageUtils::randomPath('file-storage-3');
		$this->assertEquals($result, '48' . DS . '75' . DS . '05' . DS);
	}

/**
 * testTrimPath
 *
 * @return void
 */
	public function testTrimPath() {
		$result = StorageUtils::trimPath('foobar/');
		$this->assertEquals($result, 'foobar');
	}

/**
 * testNormalizePath
 *
 * @return void
 */
	public function testNormalizePath() {
		if (DS == '\\') {
			$result = StorageUtils::normalizePath('/nice/path/test');
			$this->assertEquals($result, '\nice\path\test');
		} else {
			$result = StorageUtils::normalizePath('\nice\path\test');
			$this->assertEquals($result, '/nice/path/test');
		}
	}

/**
 * testNormalizeGlobalFilesArray
 *
 * @return void
 */

	public function testNormalizeGlobalFilesArray() {
		$data = $_FILES = array(
			'name' => array
			(
				0 => 'foo.txt',
				1 => 'bar.txt'
			),
			'type' => Array
			(
				0 => 'text/plain',
				1 => 'text/plain'
			),
			'tmp_name' => Array
			(
				0 => '/tmp/phpYzdqkD',
				1 => '/tmp/phpeEwEWG'
			),
			'error' => Array
			(
				0 => 0,
				1 => 0
			),
			'size' => Array
			(
				0 => 123,
				1 => 456
			)
		);
		$expected = [
			0 => [
				'name' => 'foo.txt',
				'type' => 'text/plain',
				'tmp_name' => '/tmp/phpYzdqkD',
				'error' => 0,
				'size' => 123
			],
			1 => [
				'name' => 'bar.txt',
				'type' => 'text/plain',
				'tmp_name' => '/tmp/phpeEwEWG',
				'error' => 0,
				'size' => 456
			]
		];

		$data = array(
			'name' => array
			(
				0 => 'foo.txt',
				1 => 'bar.txt'
			),
			'type' => Array
			(
				0 => 'text/plain',
				1 => 'text/plain'
			),
			'tmp_name' => Array
			(
				0 => '/tmp/phpYzdqkD',
				1 => '/tmp/phpeEwEWG'
			),
			'error' => Array
			(
				0 => 0,
				1 => 0
			),
			'size' => Array
			(
				0 => 123,
				1 => 456
			)
		);

		$result = StorageUtils::normalizeGlobalFilesArray($data);
		$this->assertEquals($result, $expected);

		$result = StorageUtils::normalizeGlobalFilesArray();
		$this->assertEquals($result, $expected);
	}

/**
 * testHashOperations
 *
 * @return void
 */
	public function testHashOperations() {
		$result = StorageUtils::hashOperations(array(
			'mode' => 'inbound',
			'width' => 80,
			'height' => 80
		));
		$this->assertEquals($result, '8c70933e');
	}

/**
 * testGenerateHashes
 *
 * @return void
 */
	public function testGenerateHashes() {
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

		$expected = [
			'Test' => [
				't150' => 'c3f33c2a',
				't50' => '4c34aa2e'
			],
			'UserAvatar' => [
				'small' => '19e760eb'
			]
		];
		StorageUtils::generateHashes();
		$result = Configure::read('FileStorage.imageHashes');
		$this->assertEquals($result, $expected);
	}

/**
 * testGenerateHashesRuntimeException
 *
 * @expectedException \RuntimeException
 */
	public function testGenerateHashesRuntimeException() {
		Configure::write('FileStorage.imageSizes', null);
		StorageUtils::generateHashes();
	}

/**
 * testFileExtension
 *
 * @return void
 */
	public function testFileExtension() {
		$result = StorageUtils::fileExtension($this->fileFixtures . 'titus.jpg', true);
		$this->assertEquals($result, 'jpg');

		$result = StorageUtils::fileExtension('something.else');
		$this->assertEquals($result, 'else');
	}

/**
 * testUploadArray
 *
 * @return void
 */
	public function testUploadArray() {
		$expected = [
			'name' => 'titus.jpg',
			'tmp_name' => $this->fileFixtures . 'titus.jpg',
			'error' => 0,
			'type' => 'image/jpeg',
			'size' => 332643
		];
		$result = StorageUtils::uploadArray($this->fileFixtures . 'titus.jpg');
		$this->assertEquals($result, $expected);
	}

/**
 * testGetFileHash
 *
 * @return void
 */
	public function testGetFileHash() {
		$result = StorageUtils::getFileHash($this->fileFixtures . 'titus.jpg');
		$this->assertEquals($result, 'd68da24d79835d70d5d8a544f62616d0e51af191');

		$result = StorageUtils::getFileHash($this->fileFixtures . 'titus.jpg', 'md5');
		$this->assertEquals($result, '29574141b2c44cc029828f6c5c6d3cd2');
	}

/**
 * testGetFileHashInvalidArgumentException
 *
 * @expectedException \InvalidArgumentException
 */
	public function testGetFileHashInvalidArgumentException() {
		StorageUtils::getFileHash($this->fileFixtures . 'titus.jpg', 'invalid-hash-method!');
	}
}
