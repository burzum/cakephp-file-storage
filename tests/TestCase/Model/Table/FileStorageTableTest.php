<?php

declare(strict_types=1);

namespace Burzum\FileStorage\Test\TestCase\Model\Table;

use Burzum\FileStorage\Test\TestCase\FileStorageTestCase;
use Laminas\Diactoros\UploadedFile;

/**
 * File Storage Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2020 Florian Krämer
 * @license MIT
 */
class FileStorageTableTest extends FileStorageTestCase
{
    /**
     * endTest
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->FileStorage);
        $this->getTableLocator()->clear();
    }

    /**
     * testInitialization
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->assertEquals($this->FileStorage->getTable(), 'file_storage');
        $this->assertEquals($this->FileStorage->getDisplayField(), 'filename');
    }

    /**
     * Testing a complete save call
     *
     * @link https://github.com/burzum/cakephp-file-storage/issues/85
     * @return void
     */
    public function testFileSaving()
    {
        $entity = $this->FileStorage->newEntity([
            'model' => 'Document',
            'adapter' => 'Local',
            'file' => new UploadedFile(
                $this->fileFixtures . 'titus.jpg',
                filesize($this->fileFixtures . 'titus.jpg'),
                UPLOAD_ERR_OK,
                'tituts.jpg',
                'image/jpeg'
            ),
        ], ['accessibleFields' => ['*' => true]]);
        $this->assertSame([], $entity->getErrors());

        $this->FileStorage->saveOrFail($entity);
    }

    /**
     * Testing a complete save call
     *
     * @link https://github.com/burzum/cakephp-file-storage/issues/85
     * @return void
     */
    public function testFileSavingArray()
    {
        $entity = $this->FileStorage->newEntity([
            'model' => 'Document',
            'adapter' => 'Local',
            'file' => [
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($this->fileFixtures . 'titus.jpg'),
                'type' => 'image/jpeg',
                'name' => 'tituts.jpg',
                'tmp_name' => $this->fileFixtures . 'titus.jpg',
            ],
        ], ['accessibleFields' => ['*' => true]]);
        $this->assertSame([], $entity->getErrors());

        $this->FileStorage->saveOrFail($entity);
    }
}
