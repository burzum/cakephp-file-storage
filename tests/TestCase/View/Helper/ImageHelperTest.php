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
 * ImageHelperTest
 *
 * @author Florian Krämer
 * @copy 2012 - 2016 Florian Krämer
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
	 * Image Helper
	 *
	 * @var \Cake\View\View
	 */
	public $View = null;

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
		$image = $this->ImageStorage->newEntity([
			'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
			'filename' => 'testimage.jpg',
			'model' => 'Test',
			'path' => 'test/path/',
			'extension' => 'jpg',
			'adapter' => 'Local'
		], ['accessibleFields' => ['*' => true]]);

		// Testing the old deprecated listener
		$result = $this->Image->display($image, 't150');
		$this->assertEquals($result, '<img src="/test/path/e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg" alt=""/>');

		$result = $this->Image->display($image);
		$this->assertEquals($result, '<img src="/test/path/e479b480f60b11e1a21f0800200c9a66.jpg" alt=""/>');

		// Testing the LegacyLocalFileStorageListener
		$this->_removeListeners();
		EventManager::instance()->on($this->listeners['LegacyLocalFileStorageListener']);

		$result = $this->Image->display($image, 't150');
		if (PHP_INT_SIZE === 8) {
			$this->assertEquals($result, '<img src="/img/images/10/21/10/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg" alt=""/>');
		} else {
			$this->assertEquals($result, '<img src="/img/images/86/51/86/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg" alt=""/>');
		}

		$result = $this->Image->display($image);
		if (PHP_INT_SIZE === 8) {
			$this->assertEquals($result, '<img src="/img/images/10/21/10/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.jpg" alt=""/>');
		} else {
			$this->assertEquals($result, '<img src="/img/images/86/51/86/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.jpg" alt=""/>');
		}

		// Testing the LocalListener
		$this->_removeListeners();
		EventManager::instance()->on($this->listeners['LocalListener']);

		$result = $this->Image->display($image, 't150');
		$this->assertEquals($result, '<img src="/img/Test/5c/39/33/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg" alt=""/>');

		$result = $this->Image->display($image);
		$this->assertEquals($result, '<img src="/img/Test/5c/39/33/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.jpg" alt=""/>');
	}

	/**
	 * testImage
	 *
	 * @expectedException \InvalidArgumentException
	 * @return void
	 */
	public function testImageUrlInvalidArgumentException() {
		$image = $this->ImageStorage->newEntity([
			'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
			'filename' => 'testimage.jpg',
			'model' => 'Test',
			'path' => 'test/path/',
			'extension' => 'jpg',
			'adapter' => 'Local'
		], ['accessibleFields' => ['*' => true]]);
		$this->Image->imageUrl($image, 'invalid-version!');
	}

	/**
	 * testFallbackImage
	 *
	 * @return void
	 */
	public function testFallbackImage() {
		Configure::write('Media.fallbackImages.Test.t150', 't150fallback.png');

		$result = $this->Image->fallbackImage(['fallback' => true], [], 't150');
		$this->assertEquals($result, '<img src="/img/placeholder/t150.jpg" alt=""/>');

		$result = $this->Image->fallbackImage(['fallback' => 'something.png'], [], 't150');
		$this->assertEquals($result, '<img src="/img/something.png" alt=""/>');

		$result = $this->Image->fallbackImage([], [], 't150');
		$this->assertEquals($result, '');
	}
}
