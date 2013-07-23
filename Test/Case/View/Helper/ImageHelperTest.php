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
		parent::setUp();
		$null = null;
		$this->View = new View($null);
		$this->Image = new ImageHelper($this->View);
	}

/**
 * End Test
 *
 * @return void
 * @access public
 */
	public function tearDown() {
		parent::tearDown();
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
			'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
			'model' => 'Test',
			'path' => 'test/path/',
			'extension' => 'jpg',
			'adapter' => 'Local');

		$result = $this->Image->display($image, 't150');
		$this->assertEqual($result, '<img src="/test/path/e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg" alt="" />');
	}

}