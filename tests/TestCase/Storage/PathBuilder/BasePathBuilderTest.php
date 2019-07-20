<?php
declare(strict_types=1);
namespace Burzum\FileStorage\Test\TestCase\Storage\PathBuilder;

use Burzum\FileStorage\Storage\PathBuilder\BasePathBuilder;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * TestBasePathBuilder
 */
class TestBasePathBuilder extends BasePathBuilder
{
    public function randomPathTestMethod($string)
    {
        return $string . 'test';
    }
}

/**
 * BasePathBuilderTest
 */
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
     * File Storage Table
     *
     * @var \Burzum\FileStorage\Model\Table\FileStorageTable
     */
    public $FileStorage;

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
            'adapter' => 'Local',
        ], ['accessibleFields' => ['*' => true]]);
        $this->entity->setAccess('id', true);
    }

    /**
     * testPathbuilding
     *
     * @return void
     */
    public function testPathbuilding()
    {
        $builder = new BasePathBuilder();
        $config = $builder->getConfig();

        $result = $builder->filename($this->entity);
        $this->assertEquals($result, 'filestorage1.png');

        $result = $builder->path($this->entity);
        $this->assertEquals($result, '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS);

        $result = $builder->fullPath($this->entity);
        $this->assertEquals($result, '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS . 'filestorage1.png');

        $builder->setConfig('pathPrefix', 'files');
        $result = $builder->path($this->entity);
        $this->assertEquals($result, 'files' . DS . '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS);
        $result = $builder->path($this->entity, ['pathPrefix' => 'images']);
        $this->assertEquals($result, 'images' . DS . '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS);

        $builder->setConfig('pathPrefix', 'files');
        $result = $builder->filename($this->entity);
        $this->assertEquals($result, 'filestorage1.png');

        $builder->setConfig('preserveFilename', true);
        $result = $builder->filename($this->entity);
        $this->assertEquals($result, 'cake.icon.png');

        $builder->setConfig($config);
        $builder->setConfig('pathSuffix', 'files');
        $result = $builder->path($this->entity);
        $this->assertEquals($result, '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS . 'files' . DS);

        $builder->setConfig($config);
        $builder->setConfig('pathPrefix', 'files');
        $result = $builder->path($this->entity);
        $this->assertEquals($result, 'files' . DS . '14' . DS . '83' . DS . '23' . DS . 'filestorage1' . DS);

        $builder->setConfig($config);
        $result = $builder->url($this->entity);
        $expected = '14/83/23/filestorage1/filestorage1.png';
        $this->assertEquals($result, $expected);
    }

    /**
     * testRandomPath
     *
     * @return void
     */
    public function testRandomPath()
    {
        $builder = new TestBasePathBuilder();
        $result = $builder->randomPath('test', 5, 'sha1');
        $this->assertEquals($result, '4a' . DS . '8f' . DS . 'e5' . DS . 'cc' . DS . 'b1' . DS);

        $result = $builder->randomPath('test', 3, 'sha1');
        $this->assertEquals($result, '4a' . DS . '8f' . DS . 'e5' . DS);

        $result = $builder->randomPath('test', 3, 'randomPathTestMethod');
        $this->assertEquals($result, 'testtest');
    }

    /**
     * testRandomPathInvalidArgumentException
     *
     * @return void
     */
    public function testRandomPathInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $builder = new BasePathBuilder();
        $result = $builder->randomPath('test', 5, 'does-not-exist');
    }

    /**
     * testEnsureSlash
     *
     * @return void
     */
    public function testEnsureSlash()
    {
        $string = 'foo/bar';
        $builder = new BasePathBuilder();
        $result = $builder->ensureSlash($string, 'both');
        $this->assertEquals($result, DS . $string . DS);

        $result = $builder->ensureSlash(DS . $string . DS, 'both');
        $this->assertEquals($result, DS . $string . DS);
    }

    /**
     * testEnsureSlashInvalidArgumentException
     *
     * @return void
     */
    public function testEnsureSlashInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $string = 'foo/bar';
        $builder = new BasePathBuilder();
        $builder->ensureSlash($string, 'INVALID!');
    }

    /**
     * testSplitFilename
     *
     * @return void
     */
    public function testSplitFilename()
    {
        $builder = new BasePathBuilder();
        $result = $builder->splitFilename('some.fancy.name.jpg');
        $expected = [
            'filename' => 'some.fancy.name',
            'extension' => 'jpg',
        ];
        $this->assertEquals($result, $expected);

        $result = $builder->splitFilename('no-extension');
        $expected = [
            'filename' => 'no-extension',
            'extension' => '',
        ];
        $this->assertEquals($result, $expected);
    }

    /**
     * testStripDashes
     *
     * @return void
     */
    public function testStripDashes()
    {
        $builder = new BasePathBuilder();
        $result = $builder->stripDashes('with-dashes-!');
        $this->assertEquals($result, 'withdashes!');
    }
}
