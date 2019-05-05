<?php
declare(strict_types=1);
namespace Burzum\FileStorage\Test\TestCase\Storage\PathBuilder;

use Burzum\FileStorage\Storage\PathBuilder\LegacyPathBuilder;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * LegacyPathBuilderTest
 */
class LegacyPathBuilderTest extends TestCase
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
            'id' => 'file-storage-3',
            'user_id' => 'user-1',
            'foreign_key' => 'item-1',
            'model' => 'Item',
            'filename' => 'titus.jpg',
            'filesize' => '',
            'mime_type' => 'image/jpg',
            'extension' => 'jpg',
            'hash' => '',
            'path' => '',
            'adapter' => 'Local',
            'created' => '2012-01-01 12:00:00',
            'modified' => '2012-01-01 12:00:00',
        ], ['accessibleFields' => ['*' => true]]);
        $this->entity->setAccess('id', true);
    }

    /**
     * testLegacyRecord
     *
     * @return void
     */
    public function testLegacyRecord()
    {
        $builder = new LegacyPathBuilder();
        $result = $builder->path($this->entity);
        $expected = 'files' . DS . '10' . DS . 'd3' . DS . 'b5' . DS . 'filestorage3' . DS;
        $this->assertEquals($expected, $result);

        $result = $builder->fullPath($this->entity);
        $expected = 'files' . DS . '10' . DS . 'd3' . DS . 'b5' . DS . 'filestorage3' . DS . 'filestorage3.jpg';
        $this->assertEquals($expected, $result);
    }
}
