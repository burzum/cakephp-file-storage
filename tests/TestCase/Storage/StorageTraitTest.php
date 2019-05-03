<?php
declare(strict_types=1);
namespace Burzum\FileStorage\Test\TestCase\Storage;

use Burzum\FileStorage\Storage\StorageManager;
use Burzum\FileStorage\Storage\StorageTrait;
use Cake\TestSuite\TestCase;
use Gaufrette\Filesystem;

class TestStorageTrait
{
    use StorageTrait;
}

class BasePathBuilderTest extends TestCase
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
     * setUp
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->StorageTrait = new TestStorageTrait();
    }

    /**
     * testBeforeDelete
     *
     * @return void
     */
    public function teststorageAdapter()
    {
        $result = $this->StorageTrait->getStorageAdapter('Local');
        $this->assertInstanceOf(Filesystem::class, $result);
    }

    /**
     * testBeforeDelete
     *
     * @return void
     */
    public function testGetStorageManagerInstance()
    {
        $result = $this->StorageTrait->getStorageManager();
        $this->assertInstanceOf(StorageManager::class, $result);
    }
}
