<?php
declare(strict_types=1);

namespace Burzum\FileStorage\Test\TestCase\View\Helper;

use Burzum\FileStorage\Test\TestCase\FileStorageTestCase;
use Burzum\FileStorage\View\Helper\StorageHelper;
use Cake\View\View;

/**
 * StorageHelperTest
 *
 * @author Florian Krämer
 * @copy 2012 - 2017 Florian Krämer
 * @license MIT
 */
class StorageHelperTest extends FileStorageTestCase
{
    /**
     * Image Helper
     *
     * @var ImageHelper|null
     */
    public $Storage = null;

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
        $this->Storage = new StorageHelper($this->View);
    }

    /**
     * End Test
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->Storage);
    }

    /**
     * testImage
     *
     * @return void
     */
    public function testImage()
    {
        $image = [
            'id' => 'e479b480-f60b-11e1-a21f-0800200c9a66',
            'model' => 'Test',
            'path' => 'test' . DS . 'path' . DS,
            'extension' => 'jpg',
            'adapter' => 'Local',
        ];

        $entity = $this->FileStorage->newEntity($image, ['accessibleFields' => ['*' => true]]);
        $result = $this->Storage->url($entity);
        $expected = 'Test/5c/39/33/e479b480f60b11e1a21f0800200c9a66/e479b480f60b11e1a21f0800200c9a66.jpg';
        $this->assertEquals($result, $expected);
    }
}
