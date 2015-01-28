<?php
namespace Burzum\FileStorage\Test\TestCase\Event;

use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Event\S3StorageListener;

/**
 * LocalImageProcessingListener Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 *
 * @property ImageProcessingListener $Listener
 */
class S3StorageListenerTest extends FileStorageTestCase {

/**
 * setUp
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Table = TableRegistry::get('Burzum/FileStorage.FileStorage');
		$this->Listener = $this->getMockBuilder('\Burzum\FileStorage\Event\S3StorageListener')
			->setMethods(['getAdapterConfig'])
			->getMock();
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Listener, $this->Table);
		TableRegistry::clear();
	}

/**
 * testBuildPath
 *
 * @return void
 */
	public function testBuildPath() {
		$entity = $this->Table->newEntity(array(
				'model' => 'Document',
				'adapter' => 'Test',
				'filename' => 'test.png',
				'extension' => 'png',
				'id' => '144c4170-6760-11e3-949a-0800200c9a66'
			)
		);

		$adapterConfig = array(
			'adapterClass' => '\Gaufrette\Adapter\AwsS3',
			'adapterOptions' => array(
				1 => 'my-cool-bucket'
			)
		);

		$this->Listener->expects($this->any())
			->method('getAdapterConfig')
			->with('Test')
			->will($this->returnValue($adapterConfig));

		$result = $this->Listener->buildPath($this->Table, $entity);

		$expected = array(
			'filename' => '144c4170676011e3949a0800200c9a66.png',
			'path' => '/files/Document/16/59/52/144c4170676011e3949a0800200c9a66/',
			'combined' => '/files/Document/16/59/52/144c4170676011e3949a0800200c9a66/144c4170676011e3949a0800200c9a66.png',
			'url' => 'https://my-cool-bucket.s3.amazonaws.com/files/Document/16/59/52/144c4170676011e3949a0800200c9a66/144c4170676011e3949a0800200c9a66.png'
		);

		$this->assertEquals($result, $expected);

		$result = $this->Listener->buildPath($this->Table, $entity);

		$expected = array(
			'filename' => '144c4170676011e3949a0800200c9a66.png',
			'path' => '/files/Document/16/59/52/144c4170676011e3949a0800200c9a66/',
			'combined' => '/files/Document/16/59/52/144c4170676011e3949a0800200c9a66/144c4170676011e3949a0800200c9a66.png',
			'url' => 'https://my-cool-bucket.s3.amazonaws.com/files/Document/16/59/52/144c4170676011e3949a0800200c9a66/144c4170676011e3949a0800200c9a66.png'
		);

		$this->assertEquals($result, $expected);
	}

}
