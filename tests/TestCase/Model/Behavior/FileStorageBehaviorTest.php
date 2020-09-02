<?php
declare(strict_types=1);

namespace Burzum\FileStorage\Test\TestCase\Model\Behavior;

use Burzum\FileStorage\Test\TestCase\FileStorageTestCase;
use Burzum\FileStorage\Test\TestCase\FileStorageTestTable;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;

/**
 * StorageBehaviorTest
 */
class FileStorageBehaviorTest extends FileStorageTestCase
{
    /**
     * Holds the instance of the table
     *
     * @var \Burzum\FileStorage\Model\Table\FileStorageTable|null
     */
    public $FileStorage = null;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Burzum\FileStorage.FileStorage',
    ];

    /**
     * startTest
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->getTableLocator()->clear();
        $this->FileStorage = $this->getTableLocator()->get(FileStorageTestTable::class);

        $this->FileStorage->addBehavior(
            'Burzum/FileStorage.FileStorage',
            Configure::read('FileStorage.behaviorConfig')
        );

        $this->testFilePath = Plugin::path('Burzum/FileStorage') . 'Test' . DS . 'Fixture' . DS . 'File' . DS;
    }

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
     * testAfterDelete
     *
     * @return void
     */
    public function testAfterDelete()
    {
        $file = $this->_createMockFile('\Item\00\14\90\filestorage1\filestorage1.png');
        $this->assertFileExists($file);

        $entity = $this->FileStorage->get('file-storage-1');
        $entity->adapter = 'Local';
        $entity->path = '\Item\00\14\90\filestorage1\filestorage1.png';

        $event = new Event('FileStorage.afterDelete', $this->FileStorage, [
            'entity' => $entity,
            'adapter' => 'Local',
        ]);

        $this->FileStorage->behaviors()->FileStorage->afterDelete(
            $event,
            $entity,
            new \ArrayObject([])
        );

        $this->assertFileNotExists($file);
    }

    /**
     * testBeforeSave
     *
     * @return void
     */
    public function testBeforeSave()
    {
        $entity = $this->FileStorage->newEntity([
            'file' => [
                'error' => UPLOAD_ERR_OK,
                'tmp_name' => $this->fileFixtures . 'titus.jpg',
            ],
        ], ['accessibleFields' => ['*' => true]]);

        $event = new Event('Model.beforeSave', $this->FileStorage, [
            'entity' => $entity,
        ]);

        $this->FileStorage->behaviors()->FileStorage->beforeSave($event, $entity, new \ArrayObject([]));

        $this->assertEquals($entity->adapter, 'Local');
        $this->assertEquals($entity->filesize, 332643);
        $this->assertEquals($entity->mime_type, 'image/jpeg');
        $this->assertEquals($entity->model, 'file_storage');
    }
}
