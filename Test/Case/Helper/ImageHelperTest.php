<?php
App::uses('FileStorageTestCase', 'FileStorage.TestSuite');
App::uses('ImageHelper', 'FileStorage.View/Helper');
App::uses('View', 'View'); 

class ImageHelperTest extends FileStorageTestCase {

/**
 * Gravatar helper
 *
 * @var GravatarHelper
 * @access public
 */
	public $Image = null;

/**
 * Start Test
 *
 * @return void
 * @access public
 */
	public function setUp() {
		$null = null;
		$this->View = new View($null);
		$this->Image = new ImageHelper($this->View);
		$this->Gravatar->Html = new ImageHelper($this->View);
	}

/**
 * End Test
 *
 * @return void
 * @access public
 */
	public function tearDown() {
		unset($this->Image);
	}

/**
 * testImage
 *
 * @return void
 * @access public
 */
	public function testImage() {
		$image = array(
			'id' => '32523525',
			'model' => 'Test',
			'path' => 'test/path/',
			'extension' => 'jpg',
			'adapter' => 'Local');

		$result = $this->Image->image($image, 't150');
		$this->assertEqual($result, '<img src="/test/path/32523525.c3f33c2a.jpg" alt="" />');
	}

}