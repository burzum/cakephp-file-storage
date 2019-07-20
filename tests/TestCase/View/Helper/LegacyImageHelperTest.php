<?php
declare(strict_types=1);
namespace Burzum\FileStorage\Test\TestCase\View\Helper;

use Burzum\FileStorage\Test\TestCase\FileStorageTestCase;
use Burzum\FileStorage\View\Helper\LegacyImageHelper;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\Http\ServerRequest as Request;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;

/**
 * ImageHelperTest
 *
 * @author Florian Krämer
 * @copy 2012 - 2017 Florian Krämer
 * @license MIT
 */
class LegacyImageHelperTest extends FileStorageTestCase
{
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
    public function setUp(): void
    {
        parent::setUp();
        $null = null;
        $this->View = new View($null);
        $this->Image = new LegacyImageHelper($this->View);
        $this->Image->Html = new HtmlHelper($this->View);

        $request = (new Request(['url' => 'contacts/add']))
            ->withAttribute('webroot', '/')
            ->withAttribute('base', '/');

        if (\version_compare(Configure::version(), '3.7.0', 'ge')) {
            $this->Image->Html->getView()->setRequest($request);
        } else {
            $this->Image->Html->request = $request;
        }
    }

    /**
     * End Test
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->Image);
    }

    /**
     * testImage
     *
     * @return void
     */
    public function testImage()
    {
        $image = $this->FileStorage->newEntity([
            'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
            'filename' => 'testimage.jpg',
            'model' => 'Test',
            'path' => 'test/path/',
            'extension' => 'jpg',
            'adapter' => 'Local',
        ], ['accessibleFields' => ['*' => true]]);

        // Testing the old deprecated listener
        $this->_removeListeners();
        EventManager::instance()->on($this->listeners['LegacyImageProcessingListener']);

        $result = $this->Image->display($image, 't150');
        $this->assertEquals('<img src="/test/path/e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg" alt=""/>', $result);

        $result = $this->Image->display($image);
        $this->assertEquals('<img src="/test/path/e479b480f60b11e1a21f0800200c9a66.jpg" alt=""/>', $result);

        // Testing the LegacyLocalFileStorageListener
        $this->_removeListeners();
        EventManager::instance()->on($this->listeners['LegacyLocalListenerImageProcessing']);

        $result = $this->Image->display($image, 't150');
        if (PHP_INT_SIZE === 8) {
            $this->assertEquals('<img src="/img/images/5c/39/33/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg" alt=""/>', $result);
        } else {
            $this->assertEquals('<img src="/img/images/86/51/86/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg" alt=""/>', $result);
        }

        $result = $this->Image->display($image);
        if (PHP_INT_SIZE === 8) {
            $this->assertEquals('<img src="/img/images/5c/39/33/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.jpg" alt=""/>', $result);
        } else {
            $this->assertEquals('<img src="/img/images/86/51/86/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.jpg" alt=""/>', $result);
        }

        // Testing the LocalListener
        $this->_removeListeners();
        EventManager::instance()->on($this->listeners['LocalListener']);

        $result = $this->Image->display($image, 't150');
        $this->assertEquals('<img src="/img/Test/5c/39/33/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.c3f33c2a.jpg" alt=""/>', $result);

        $result = $this->Image->display($image);
        $this->assertEquals('<img src="/img/Test/5c/39/33/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.jpg" alt=""/>', $result);
    }

    /**
     * testImage
     *
     * @return void
     */
    public function testImageUrlInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $image = $this->FileStorage->newEntity([
            'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
            'filename' => 'testimage.jpg',
            'model' => 'Test',
            'path' => 'test/path/',
            'extension' => 'jpg',
            'adapter' => 'Local',
        ], ['accessibleFields' => ['*' => true]]);
        $this->Image->imageUrl($image, 'invalid-version!');
    }

    /**
     * testFallbackImage
     *
     * @return void
     */
    public function testFallbackImage()
    {
        Configure::write('Media.fallbackImages.Test.t150', 't150fallback.png');

        $result = $this->Image->fallbackImage(['fallback' => true], [], 't150');
        $this->assertEquals($result, '<img src="/img/placeholder/t150.jpg" alt=""/>');

        $result = $this->Image->fallbackImage(['fallback' => 'something.png'], [], 't150');
        $this->assertEquals($result, '<img src="/img/something.png" alt=""/>');

        $result = $this->Image->fallbackImage([], [], 't150');
        $this->assertEquals($result, '');
    }
}
