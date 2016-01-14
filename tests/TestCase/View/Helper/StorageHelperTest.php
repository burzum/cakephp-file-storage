<?php
namespace Burzum\FileStorage\Test\TestCase\View\Helper;

use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\View\Helper\StorageHelper;
use Cake\ORM\TableRegistry;
use Cake\View\View;
use Cake\View\Helper\HtmlHelper;
use Cake\Network\Request;
use Cake\Core\Configure;
use Cake\Event\EventManager;

/**
 * StorageHelperTest
 *
 * @author Florian Krämer
 * @copy 2012 - 2016 Florian Krämer
 * @license MIT
 */
class StorageHelperTest extends FileStorageTestCase {

/**
 * Image Helper
 *
 * @var ImageHelper
 */
	public $Storage = null;

/**
 * Start Test
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$null = null;
		$this->View = new View($null);
		$this->Storage = new StorageHelper($this->View);
	}

/**
 * End Test
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Storage);
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
			'path' => 'test' . DS . 'path' . DS,
			'extension' => 'jpg',
			'adapter' => 'Local'
		);

		$entity = $this->FileStorage->newEntity($image, ['accessibleFields' => ['*' => true]]);
		$result = $this->Storage->url($entity);
		$expected = 'Test/5c/39/33/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.jpg';
		$this->assertEquals($result, $expected);
	}
}
