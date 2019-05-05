<?php
declare(strict_types=1);
namespace Burzum\FileStorage\Test\TestCase\Storage\PathBuilder;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * S3PathBuilderTest
 */
class S3PathBuilderTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Burzum\FileStorage.FileStorage',
    ];

    /**
     * File Storage Table
     *
     * @var \Burzum\FileStorage\Model\Table\FileStorageTable
     */
    public $FileStorage;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->FileStorage = TableRegistry::getTableLocator()->get('Burzum/FileStorage.FileStorage');
        $this->entity = $this->FileStorage->newEntity([
            'id' => 'file-storage-1',
            'user_id' => 'user-1',
            'foreign_key' => 'item-1',
            'model' => 'Item',
            'filename' => 'cake.icon.png',
            'filesize' => '',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'hash' => '',
            'path' => '',
            'adapter' => 'S3',
        ], ['accessibleFields' => ['*' => true]]);
        $this->entity->setAccess('id', true);

//        $S3Client = \Aws\S3\S3Client::factory([
//          'key' => 'YOUR-KEY',
//          'secret' => 'YOUR-SECRET'
//        ]);

//        StorageManager::config('S3Image', [
//          'adapterOptions' => [
//              '',
//              'YOUR-BUCKET-HERE',
//              [],
//              true,
//          ],
//          'adapterClass' => '\Gaufrette\Adapter\AwsS3',
//          'class' => '\Gaufrette\Filesystem',
//        ]);
    }

    /**
     * testUrl
     *
     * @return void
     * @todo finish me
     */
    public function testUrl()
    {
        $this->markTestIncomplete();
        // $builder = new S3PathBuilder();
        // $result = $builder->url($this->entity);
    }
}
