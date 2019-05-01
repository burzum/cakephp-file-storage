<?php
declare(strict_types=1);
namespace Burzum\FileStorage\Test\TestCase\Model\Behavior;

use Burzum\FileStorage\Test\TestCase\FileStorageTestCase;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class FileStorageTestTable extends Table
{
    /**
     * Initialize
     *
     * @param array $config
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('file_storage');
        $this->addBehavior('Burzum/FileStorage.FileStorage');
    }
}

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
    public function setUp()
    {
        parent::setUp();
        $this->FileStorage = TableRegistry::getTableLocator()->get('Burzum/FileStorage.FileStorage');
        $this->FileStorage->addBehavior('Burzum/FileStorage.FileStorage');
        $this->testFilePath = Plugin::path('Burzum/FileStorage') . 'Test' . DS . 'Fixture' . DS . 'File' . DS;
    }

    /**
     * endTest
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->FileStorage);
        TableRegistry::getTableLocator()->clear();
    }

/**
 * testGetFileInfoFromUpload
 *
 * @return void
 */
//  public function testGetFileInfoFromUpload() {
//      $filename = Plugin::path('Burzum/FileStorage') . DS . 'tests' . DS . 'Fixture' . DS . 'File' . DS . 'titus.jpg';
//
//      $data = new \ArrayObject([
//          'file' => [
//              'name' => 'titus.jpg',
//              'tmp_name' => $filename
//          ]
//      ]);
//
//      $this->FileStorage->getFileInfoFromUpload($data);
//
//      $this->assertEquals(332643, $data['filesize']);
//      $this->assertEquals('image/jpeg', $data['mime_type']);
//      $this->assertEquals('jpg', $data['extension']);
//  }

    /**
     * testAfterDelete
     *
     * @return void
     */
    public function testAfterDelete()
    {
        $this->_createMockFile('\Item\14\83\23\filestorage1\filestorage1.png');

        $entity = $this->FileStorage->get('file-storage-1');
        $entity->adapter = 'Local';
        $event = new Event('FileStorage.afterDelete', $this->FileStorage, [
            'entity' => $entity,
            'adapter' => 'Local',
        ]);

        $this->FileStorage->behaviors()->FileStorage->afterDelete($event, $entity, []);
        $this->markTestIncomplete();

        // Testing the case the file does not exist
//      $entity = $this->FileStorage->get('file-storage-1');
//      $entity->adapter = 'Local';
//      $entity->path = 'does-not-exist!';
//      $event = new Event('FileStorage.afterDelete',  $this->FileStorage, [
//          'entity' => $entity,
//          'adapter' => 'Local'
//      ]);
//      $this->FileStorage->behaviors()->FileStorage->afterDelete($event, $entity, []);
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

        $this->FileStorage->behaviors()->FileStorage->beforeSave($event, $entity);

        $this->assertEquals($entity->adapter, 'Local');
        $this->assertEquals($entity->filesize, 332643);
        $this->assertEquals($entity->mime_type, 'image/jpeg');
        $this->assertEquals($entity->model, 'file_storage');
    }
}
