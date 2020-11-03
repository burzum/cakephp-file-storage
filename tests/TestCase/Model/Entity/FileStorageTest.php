<?php

declare(strict_types=1);

namespace Burzum\FileStorage\Test\TestCase\Model\Entity;

use Burzum\FileStorage\Model\Entity\FileStorage;
use Burzum\FileStorage\Storage\Listener\LocalListener;
use Burzum\FileStorage\Test\TestCase\FileStorageTestCase;


/**
 * File Storage Entity Test
 *
 * @author Florian Kr�mer
 * @copyright 2012 - 2017 Florian Kr�mer
 * @license MIT
 */
class FileStorageTest extends FileStorageTestCase
{
    /**
     * @return void
     */
    public function testGetVariantUrl(): void
    {
        $fileStorage = new FileStorage();

        $result = $fileStorage->getVariantUrl('nonexistent');
        $this->assertNull($result);
    }

    /**
     * @return void
     */
    public function testGetVariantPath(): void
    {
        $fileStorage = new FileStorage();

        $result = $fileStorage->getVariantPath('nonexistent');
        $this->assertNull($result);
    }
}
