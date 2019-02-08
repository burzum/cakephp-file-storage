<?php
namespace Burzum\FileStorage\Test\TestCase\View\Helper;

use Burzum\FileStorage\Test\TestCase\FileStorageTestCase;
use Burzum\FileStorage\View\Helper\ImageHelper;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;

/**
 * ImageHelperTest
 *
 * @author Florian Krämer
 * @copy 2012 - 2017 Florian Krämer
 * @license MIT
 */
class ImageHelperTest extends FileStorageTestCase {

	/**
	 * Image Helper
	 *
	 * @var \Burzum\FileStorage\View\Helper\ImageHelper|null
	 */
	public $Image = null;

	/**
	 * Image Helper
	 *
	 * @var \Cake\View\View|null
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

		$request = (new Request(['url' => 'contacts/add']))
			->withAttribute('webroot', '/')
			->withAttribute('base', '/');

		$this->Image->Html->getView()->setRequest($request);
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
	 * testImageUrl
	 *
	 * @return void
	 */
	public function testImageUrl() {
		$image = $this->FileStorage->newEntity([
			'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
			'filename' => 'testimage.jpg',
			'model' => 'Test',
			'path' => 'test/path/testimage.jpg',
			'extension' => 'jpg',
			'adapter' => 'Local'
		], ['accessibleFields' => ['*' => true]]);

		$result = $this->Image->imageUrl($image, 't150', ['pathPrefix' => '/src/']);
		$this->assertEquals('/src/test/path/testimage.c3f33c2a.jpg', $result);
	}

	/**
	 * testImage
	 *
	 * @expectedException \InvalidArgumentException
	 * @return void
	 */
	public function testImageUrlInvalidArgumentException() {
		$image = $this->FileStorage->newEntity([
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
