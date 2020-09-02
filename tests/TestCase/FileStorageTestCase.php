<?php
declare(strict_types=1);

namespace Burzum\FileStorage\Test\TestCase;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Filesystem\Folder;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

/**
 * FileStorageTestCase
 *
 * @author Florian Krämer
 * @copyright 2012 - 2020 Florian Krämer
 * @license MIT
 */
class FileStorageTestCase extends TestCase
{
    use LocatorAwareTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Burzum\FileStorage.FileStorage',
    ];

    /**
     * FileStorage Table instance.
     *
     * @var \Burzum\FileStorage\Model\Table\FileStorageTable
     */
    public $FileStorage;

    /**
     * Path to the file fixtures, set in the setUp() method.
     *
     * @var string
     */
    public string $fileFixtures;

    /**
     * Test file path
     *
     * @var string
     */
    public string $testPath = '';

    /**
     * Setup test folders and files
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->testPath = TMP . 'file-storage-test' . DS;
        $this->fileFixtures = Plugin::path('Burzum/FileStorage') . 'tests' . DS . 'Fixture' . DS . 'File' . DS;

        if (!is_dir($this->testPath)) {
            mkdir($this->testPath);
        }

        $this->prepareDependencies();
        $this->configureImageVariants();

        $this->FileStorage = $this
            ->getTableLocator()
            ->get(FileStorageTestTable::class);
    }

    /**
     * @return void
     */
    private function configureImageVariants(): void
    {
        Configure::write('FileStorage.imageVariants', [
            'Test' => [
                't50' => [
                    'thumbnail' => [
                        'mode' => 'outbound',
                        'width' => 50,
                        'height' => 50
                    ]
                ],
                't150' => [
                    'thumbnail' => [
                        'mode' => 'outbound',
                        'width' => 150,
                        'height' => 150,
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
    }

    /**
     * @return void
     */
    private function prepareDependencies(): void
    {
        $pathBuilder = new \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder([
            'pathTemplate' => '{model}{ds}{collection}{ds}{randomPath}{ds}{strippedId}{ds}{strippedId}.{extension}',
            'variantPathTemplate' => '{model}{ds}{collection}{ds}{randomPath}{ds}{strippedId}{ds}{strippedId}.{hashedVariant}.{extension}',
        ]);

        $storageService = new \Phauthentic\Infrastructure\Storage\StorageService(
            new \Phauthentic\Infrastructure\Storage\StorageAdapterFactory()
        );

        $storageService->setAdapterConfigFromArray([
            'Local' => [
                'class' => \Phauthentic\Infrastructure\Storage\Factories\LocalFactory::class,
                'options' => [
                    'root' => $this->testPath, true
                ]
            ],
        ]);

        $fileStorage = new \Phauthentic\Infrastructure\Storage\FileStorage(
            $storageService,
            $pathBuilder
        );

        $imageManager = new \Intervention\Image\ImageManager([
            'driver' => 'gd'
        ]);

        $imageProcessor = new \Phauthentic\Infrastructure\Storage\Processor\Image\ImageProcessor(
            $fileStorage,
            $pathBuilder,
            $imageManager
        );

        Configure::write('FileStorage.behaviorConfig', [
            'fileStorage' => $fileStorage,
            'imageProcessor' => $imageProcessor
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

        $this->getTableLocator()->clear();
        $Folder = new Folder($this->testPath);
        $Folder->delete();
    }

    /**
     * Creates a file
     *
     * @string $file File path and name, relative to FileStorageTestCase::$testPath
     * @return string
     */
    protected function _createMockFile($file): string
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

        return $this->testPath . $file;
    }
}
