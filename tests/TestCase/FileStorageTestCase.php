<?php
declare(strict_types=1);
namespace Burzum\FileStorage\Test\TestCase;

use Burzum\FileStorage\Storage\Listener\LegacyImageProcessingListener;
use Burzum\FileStorage\Storage\Listener\LegacyLocalFileStorageListener;
use Burzum\FileStorage\Storage\Listener\LocalListener;
use Burzum\FileStorage\Storage\StorageManager;
use Burzum\FileStorage\Storage\StorageUtils;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\EventManager;
use Cake\Filesystem\Folder;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * FileStorageTestCase
 *
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
class FileStorageTestCase extends TestCase
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
     * Listeners to be used in tests.
     *
     * @var array
     */
    public $listeners = [];

    /**
     * FileStorage Table instance.
     *
     * @var \Burzum\FileStorage\Model\Table\FileStorageTable
     */
    public $FileStorage;

    /**
     * ImageStorage Table instance.
     *
     * @var \Burzum\FileStorage\Model\Table\ImageStorageTable
     */
    public $ImageStorage;

    /**
     * Path to the file fixtures, set in the setUp() method.
     *
     * @var string
     */
    public $fileFixtures;

    /**
     * Test file path
     *
     * @var string
     */
    public $testPath;

    /**
     * Setup test folders and files
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_setupListeners();

        $this->testPath = TMP . 'file-storage-test' . DS;
        $this->fileFixtures = Plugin::path('Burzum/FileStorage') . 'tests' . DS . 'Fixture' . DS . 'File' . DS;

        if (!is_dir($this->testPath)) {
            mkdir($this->testPath);
        }

        Configure::write('FileStorage.basePath', $this->testPath);
        Configure::write('FileStorage.imageSizes', [
            'Test' => [
                't50' => [
                    'thumbnail' => [
                        'mode' => 'outbound',
                        'width' => 50, 'height' => 50]],
                't150' => [
                    'thumbnail' => [
                        'mode' => 'outbound',
                        'width' => 150, 'height' => 150,
                    ],
                ],
            ],
            'UserAvatar' => [
                'small' => [
                    'thumbnail' => [
                        'mode' => 'inbound',
                        'width' => 80,
                        'height' => 80,
                    ],
                ],
            ],
        ]);

        StorageUtils::generateHashes();

        StorageManager::config('Local', [
            'adapterOptions' => [$this->testPath, true],
            'adapterClass' => '\Gaufrette\Adapter\Local',
            'class' => '\Gaufrette\Filesystem',
        ]);

        $this->FileStorage = TableRegistry::getTableLocator()->get('Burzum/FileStorage.FileStorage');
    }

    /**
     * Setting up the listeners.
     *
     * @return void
     */
    protected function _setupListeners()
    {
        $this->listeners['LocalListener'] = new LocalListener();
        $this->listeners['LocalListenerImageProcessing'] = new LocalListener([
            'imageProcessing' => true,
        ]);

        $this->listeners['LegacyLocalListener'] = new LegacyLocalFileStorageListener([
            'disableDeprecationWarning' => true,
        ]);

        $this->listeners['LegacyLocalListenerImageProcessing'] = new LegacyLocalFileStorageListener([
            'imageProcessing' => true,
            'disableDeprecationWarning' => true,
        ]);

        $this->listeners['LegacyImageProcessingListener'] = new LegacyImageProcessingListener([
            'disableDeprecationWarning' => true,
        ]);
    }

    /**
     * Cleanup test files
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        $this->_removeListeners();

        TableRegistry::getTableLocator()->clear();
        $Folder = new Folder($this->testPath);
        $Folder->delete();
    }

    /**
     * Helper method to remove all listeners.
     *
     * @return void
     */
    protected function _removeListeners()
    {
        foreach ($this->listeners as $listener) {
            EventManager::instance()->off($listener);
        }
    }

    /**
     * Creates a file
     *
     * @string $file File path and name, relative to FileStorageTestCase::$testPath
     * @return void
     */
    protected function _createMockFile($file)
    {
        if (DS === '/') {
            $file = str_replace('\\', DS, $file);
        } else {
            $file = str_replace('/', DS, $file);
        }
        $path = dirname($file);
        if (!is_dir($this->testPath . $path)) {
            mkdir($this->testPath . $path, 0777, true);
        }
        if (!file_exists($this->testPath . $file)) {
            touch($this->testPath . $file);
        }
    }
}
