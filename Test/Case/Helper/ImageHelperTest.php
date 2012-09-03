<?php
App::uses('ImageHelper', 'FileStorage.View/Helper');
App::uses('View', 'View'); 

class ImageHelperTest extends CakeTestCase {

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
 * testBaseUrlGeneration
 *
 * @return void
 * @access public
 */
	public function testDisplay() {
		$image = array(
			'id' => '32523525',
			'model' => 'Article',
			'path' => '/test/path/',
			'extension' => 'jpg',
			'adapter' => 'Local');
		$result = $this->Image->image($image, 't150');
	}

}
