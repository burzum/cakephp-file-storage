<?php
namespace Burzum\FileStorage\Test\TestCase\View\Helper;

use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\View\Helper\ImageHelper;
use Cake\View\View;
use Cake\View\Helper\HtmlHelper;
use Cake\Network\Request;
use Cake\Core\Configure;
use Cake\Event\EventManager;

/**
 * S3StorageListener
 *
 * @author Florian Krämer
 * @copy 2012 - 2014 Florian Krämer
 * @license MIT
 */
class ImageHelperTest extends FileStorageTestCase {

/**
 * Image Helper
 *
 * @var ImageHelper
 */
	public $Image = null;

/**
 * Start Test
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$null = null;
		$this->View = new View($null);
		$this->Image = new ImageHelper($this->View);
		$this->Image->Html = new HtmlHelper($this->View);
		$this->Image->Html->request = new Request('contacts/add', false);
		$this->Image->Html->request->webroot = '/';
		$this->Image->Html->request->base = '/';
	}

/**
 * End Test
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Image);
	}

/**
 * testImage
 *
 * @return void
 */
	public function testImage() {
		$image = array(
			'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
			'model' => 'Test',
			'path' => 'test/path/',
			'extension' => 'jpg',
			'adapter' => 'Local'
		);

		$result = $this->Image->display($image, 't150');
		$this->assertEquals($result, '<img src="/test/path/e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg" alt=""/>');
	}

/**
 * testImage
 *
 * @expectedException \InvalidArgumentException
 * @return void
 */
	public function testImageUrlInvalidArgumentException() {
		$image = array(
			'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
			'model' => 'Test',
			'path' => 'test/path/',
			'extension' => 'jpg',
			'adapter' => 'Local'
		);
		$this->Image->imageUrl($image, 'invalid-version!');
	}

/**
 * testFallbackImage
 *
 * @return void
 */
	public function testFallbackImage() {
		Configure::write('Media.fallbackImages.Test.t150', 't150fallback.png');

		$result = $this->Image->fallbackImage(array('fallback' => true), array(), 't150');
		$this->assertEquals($result, '<img src="/img/placeholder/t150.jpg" alt=""/>');

		$result = $this->Image->fallbackImage(array('fallback' => 'something.png'), array(), 't150');
		$this->assertEquals($result, '<img src="/img/something.png" alt=""/>');

		$result = $this->Image->fallbackImage(array(), array(), 't150');
		$this->assertEquals($result, '');
	}

}