<?php
declare(strict_types = 1);

namespace Burzum\FileStorage\Test\TestCase\View\Helper;

use Burzum\FileStorage\Test\TestCase\FileStorageTestCase;
use Burzum\FileStorage\View\Helper\ImageHelper;
use Cake\Core\Configure;
use Cake\Http\ServerRequest as Request;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;
use Phauthentic\Infrastructure\Storage\Processor\Exception\VariantDoesNotExistException;

/**
 * ImageHelperTest
 *
 * @author Florian Krämer
 * @copy 2012 - 2017 Florian Krämer
 * @license MIT
 */
class ImageHelperTest extends FileStorageTestCase
{
    /**
     * Image Helper
     *
     * @var \Burzum\FileStorage\View\Helper\ImageHelper
     */
    protected $helper;

    /**
     * Image Helper
     *
     * @var \Cake\View\View
     */
    protected $view;

    /**
     * Start Test
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $null = null;
        $this->view = new View($null);
        $this->helper = new ImageHelper($this->view);
        $this->helper->Html = new HtmlHelper($this->view);

        $request = (new Request(['url' => 'contacts/add']))
            ->withAttribute('webroot', '/')
            ->withAttribute('base', '/');

        $this->helper->Html->getView()->setRequest($request);
    }

    /**
     * End Test
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->helper);
    }

    /**
     * testImageUrl
     *
     * @return void
     */
    public function testImageUrl()
    {
        $image = $this->FileStorage->newEntity([
            'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
            'filename' => 'testimage.jpg',
            'model' => 'Test',
            'path' => 'test/path/testimage.jpg',
            'extension' => 'jpg',
            'adapter' => 'Local',
            'variants' => [
                't150' => [
                    'path' => 'test/path/testimage.c3f33c2a.jpg',
                    'url' => '',
                ],
            ],
        ], ['accessibleFields' => ['*' => true]]);

        $result = $this->helper->imageUrl($image, 't150', ['pathPrefix' => '/src/']);
        $this->assertEquals('/src/test/path/testimage.c3f33c2a.jpg', $result);

        $result = $this->helper->imageUrl($image, null, ['pathPrefix' => '/src/']);
        $this->assertEquals('/src/test/path/testimage.jpg', $result);
    }

    /**
     * testImage
     *
     * @return void
     */
    public function testImageUrlInvalidArgumentException()
    {
        $this->expectException(VariantDoesNotExistException::class);
        $image = $this->FileStorage->newEntity([
            'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
            'filename' => 'testimage.jpg',
            'model' => 'Test',
            'path' => 'test/path/',
            'extension' => 'jpg',
            'adapter' => 'Local',
        ], ['accessibleFields' => ['*' => true]]);

        $this->helper->imageUrl($image, 'invalid-version!');
    }

    /**
     * testFallbackImage
     *
     * @return void
     */
    public function testFallbackImage()
    {
        Configure::write('Media.fallbackImages.Test.t150', 't150fallback.png');

        $result = $this->helper->fallbackImage(['fallback' => true], 't150');
        $this->assertSame('<img src="/img/placeholder/t150.jpg" alt=""/>', $result);

        $result = $this->helper->fallbackImage(['fallback' => 'something.png'], 't150');
        $this->assertSame('<img src="/img/something.png" alt=""/>', $result);

        $result = $this->helper->fallbackImage([], 't150');
        $this->assertSame('', $result);
    }
}
