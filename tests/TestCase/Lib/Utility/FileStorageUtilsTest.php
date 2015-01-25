<?php
namespace Burzum\FileStorage\Test\TestCase\Lib\Utility;

use Cake\Core\Configure;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Lib\FileStorageUtils;

/**
 * StorageManagerTest
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class FileStorageUtilsTest extends FileStorageTestCase {

/**
 * testRandomPath
 *
 * @return void
 */
	public function testRandomPath() {
		$result = FileStorageUtils::randomPath('someteststring');
		$this->assertEquals($result, '38' . DS . '88' . DS . '98' . DS);
	}

/**
 * testTrimPath
 *
 * @return void
 */
	public function testTrimPath() {
		$result = FileStorageUtils::trimPath('foobar/');
		$this->assertEquals($result, 'foobar');
	}

/**
 * testNormalizePath
 *
 * @return void
 */
	public function testNormalizePath() {
		if (DS == '\\') {
			$result = FileStorageUtils::normalizePath('/nice/path/test');
			$this->assertEquals($result, '\nice\path\test');
		} else {
			$result = FileStorageUtils::normalizePath('\nice\path\test');
			$this->assertEquals($result, '/nice/path/test');
		}
	}

	public function testNormalizeGlobalFilesArray() {
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
		$expected = [
			'name' => [
				0 => [
					0 => 'foo.txt',
					1 => 'bar.txt'
				]
			],
			'type' => [
				0 => [
					0 => 'text/plain',
					1 => 'text/plain'
				]
			],
			'tmp_name' => [
				0 => [
					0 => '/tmp/phpYzdqkD',
					1 => '/tmp/phpeEwEWG'
				]
			],
			'error' => [
				0 => [
					0 => 0,
					1 => 0
				]
			],
			'size' => [
				0 => [
					0 => 123,
					1 => 456
				]
			]
		];
		$result = FileStorageUtils::normalizeGlobalFilesArray($data);
		$this->assertEquals($result, $expected);
	}

	public function testHashOperations() {
		$result = FileStorageUtils::hashOperations(array(
			'mode' => 'inbound',
			'width' => 80,
			'height' => 80
		));
		$this->assertEquals($result, '8c70933e');
	}

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
		FileStorageUtils::generateHashes();
		$result = Configure::read('FileStorage.imageHashes');
		$this->assertEquals($result, $expected);
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
		$result = FileStorageUtils::uploadArray($this->fileFixtures . 'titus.jpg');
		$this->assertEquals($result, $expected);
	}

}
