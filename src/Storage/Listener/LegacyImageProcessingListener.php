<?php
declare(strict_types=1);
namespace Burzum\FileStorage\Storage\Listener;

use Burzum\FileStorage\Storage\StorageManager;
use Burzum\FileStorage\Storage\StorageUtils;
use Burzum\Imagine\Lib\ImageProcessor;
use Burzum\Imagine\Lib\ImagineUtility;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventInterface;
use Cake\ORM\Table;

/**
 * @author Florian Krämer
 * @copy 2013 - 2015 Florian Krämer
 * @license MIT
 *
 * @deprecated This listener class is deprecated
 */
class LegacyImageProcessingListener extends AbstractListener
{
    /**
     * Default settings
     *
     * @var array
     */
    protected $_defaultConfig = [
        'pathBuilder' => 'Base',
        'pathBuilderOptions' => [
//          'pathPrefix' => 'files',
//          'modelFolder' => false,
            'preserveFilename' => false,
//          'randomPath' => 'sha1'
        ],
        'disableDeprecationWarning' => false,
    ];

    /**
     * The adapter class
     *
     * @var ?string
     */
    public $adapterClass = null;

    /**
     * ImageProcessor instance
     *
     * @var \Burzum\Imagine\Lib\ImageProcessor
     */
    public $_imageProcessor = null;

    /**
     * Name of the storage table class name the event listener requires the table
     * instances to extend.
     * This information is important to know when to use the event callbacks or not.
     * Must be \FileStorage\Model\Table\FileStorageTable or \FileStorage\Model\Table\ImageStorageTable
     *
     * @var string
     */
    public $storageTableClass = '\Burzum\FileStorage\Model\Table\ImageStorageTable';

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->setConfig('autoRotate', []);
        $this->setConfig($config);
        $this->_imageProcessor = new ImageProcessor();

        if ($this->_config['disableDeprecationWarning'] !== true) {
            user_error('LegacyLocalFileStorageListener is deprecated! Please migrate to another listener! Or set the disableDeprecationWarning option to true.', E_USER_DEPRECATED);
        }
    }

    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents(): array
    {
        return [
            'ImageVersion.createVersion' => 'createVersions',
            'ImageVersion.removeVersion' => 'removeVersions',
            'ImageVersion.getVersions' => 'imagePath',
            'ImageStorage.beforeSave' => 'beforeSave',
            'ImageStorage.afterSave' => 'afterSave',
            'ImageStorage.afterDelete' => 'afterDelete',
            'FileStorage.ImageHelper.imagePath' => 'imagePath', // Deprecated
        ];
    }

    /**
     * Auto rotates the image if an orientation in the exif data is found that is not 0.
     *
     * @param string $imageFile Path to the image file.
     * @param string $format Format of the image to save. Workaround for imagines save(). :(
     * @return boolean
     */
    protected function _autoRotate(string $imageFile, string $format): bool
    {
        $orientation = ImagineUtility::getImageOrientation($imageFile);
        $degree = 0;
        if ($orientation === null) {
            return false;
        }
        if ($orientation === 0) {
            return true;
        }
        switch ($orientation) {
            case 180:
                $degree = -180;
                break;
            case -90:
                $degree = 90;
                break;
            case 90:
                $degree = -90;
                break;
        }
        $processor = new ImageProcessor();
        $processor->open($imageFile);
        $processor->rotate(['degree' => $degree]);
        $processor->save($imageFile, ['format' => $format]);

        return true;
    }

    /**
     * Creates the different versions of images that are configured
     *
     * @param \Cake\ORM\Table $table
     * @param EntityInterface $entity
     * @param array $operations
     * @throws \Burzum\FileStorage\Event\Exception
     * @throws \Exception
     * @return bool
     */
    protected function _createVersions(Table $table, EntityInterface $entity, array $operations): bool
    {
        $Storage = StorageManager::adapter($entity['adapter']);
        $path = $this->_buildPath($entity, true);
        $tmpFile = $this->_tmpFile($Storage, $path, TMP . 'image-processing');

        foreach ($operations as $version => $imageOperations) {
            $hash = StorageUtils::hashOperations($imageOperations);
            $string = $this->_buildPath($entity, true, $hash);

            if ($this->adapterClass === 'AmazonS3' || $this->adapterClass === 'AwsS3') {
                $string = str_replace('\\', '/', $string);
            }

            if ($Storage->has($string)) {
                return false;
            }

            try {
                $image = $table->processImage($tmpFile, null, ['format' => $entity['extension']], $imageOperations);
                $Storage->write($string, $image->get($entity['extension']), true);
            } catch (\Exception $e) {
                $this->log($e->getMessage());
                unlink($tmpFile);
                throw $e;
            }
        }

        unlink($tmpFile);
        return true;
    }

    /**
     * Creates versions for a given image record
     *
     * @param \Cake\Event\EventInterface  $Event
     * @return void
     */
    public function createVersions(EventInterface  $Event): void
    {
        if ($this->_checkEvent($Event)) {
            $table = $Event->getSubject();
            $record = $Event->getData('record');
            $this->_createVersions($table, $record, $Event->getData('operations'));
            $Event->stopPropagation();
        }
    }

    /**
     * Removes versions for a given image record
     *
     * @param \Cake\Event\Event $Event
     */
    public function removeVersions(Event $Event): void
    {
        $this->_removeVersions($Event);
    }

    /**
     * Removes versions for a given image record
     *
     * @param \Cake\Event\Event $Event
     * @return void
     */
    protected function _removeVersions(Event $Event): void
    {
        if ($this->_checkEvent($Event)) {
            $data = $Event->getData();

            $Storage = $data['storage'];
            $record = $data['record'];
            foreach ($data['operations'] as $version => $operations) {
                $hash = StorageUtils::hashOperations($operations);
                $string = $this->_buildPath($record, true, $hash);
                if ($this->adapterClass === 'AmazonS3' || $this->adapterClass === 'AwsS3') {
                    $string = str_replace('\\', '/', $string);
                }
                try {
                    if ($Storage->has($string)) {
                        $Storage->delete($string);
                    }
                } catch (\Exception $e) {
                    $this->log($e->getMessage());
                }
            }
            $Event->stopPropagation();
        }
    }

    /**
     * afterDelete
     *
     * @param \Cake\Event\Event $Event
     * @return bool|null
     * @throws \Burzum\FileStorage\Storage\StorageException
     */
    public function afterDelete(Event $Event): bool
    {
        if ($this->_checkEvent($Event)) {
            $record = $Event->getData('record');
            $string = $this->_buildPath($record, true, null);
            if ($this->adapterClass === 'AmazonS3' || $this->adapterClass === 'AwsS3') {
                $string = str_replace('\\', '/', $string);
            }
            try {
                $Storage = StorageManager::adapter($record['adapter']);
                if (!$Storage->has($string)) {
                    $Event->stopPropagation();
                    $Event->result = false;

                    return false;
                }
                $Storage->delete($string);
            } catch (\Exception $e) {
                $this->log($e->getMessage());
                $Event->stopPropagation();
                $Event->setResult(false);

                return false;
            }
            $operations = Configure::read('FileStorage.imageSizes.' . $record['model']);
            if (!empty($operations)) {
                $Event->setData('operations', $operations);
                $this->_removeVersions($Event);
            }
            $Event->stopPropagation();
            $Event->setResult(true);

            return true;
        }
    }

    /**
     * beforeSave
     *
     * @param \Cake\Event\Event $Event
     * @return void
     * @throws \Burzum\FileStorage\Storage\StorageException
     */
    public function beforeSave(Event $Event): void
    {
        if ($this->_checkEvent($Event)) {
            $data = $Event->getData();

            if (in_array($data['record']['model'], (array)$this->getConfig('autoRotate'))) {
                $imageFile = $data['record']['file']['tmp_name'];
                $format = StorageUtils::fileExtension($data['record']['file']['name']);
                $this->_autoRotate($imageFile, $format);
            }
        }
    }

    /**
     * afterSave
     *
     * @param \Cake\Event\Event $Event
     * @return void
     * @throws \Burzum\FileStorage\Storage\StorageException
     */
    public function afterSave(Event $Event): void
    {
        if ($this->_checkEvent($Event)) {
            $table = $Event->getSubject();
            $record = $Event->getData('record');
            $Storage = StorageManager::adapter($record->adapter);
            try {
                $id = $record->{$table->primaryKey()};
                $filename = $this->stripDashes($id);
                $file = $record['file'];
                $record['path'] = $this->fsPath('images' . DS . $record['model'], $id);
                if ($this->_config['preserveFilename'] === true) {
                    $path = $record['path'] . $record['filename'];
                } else {
                    $path = $record['path'] . $filename . '.' . $record['extension'];
                }

                if ($this->adapterClass === 'AmazonS3' || $this->adapterClass === 'AwsS3') {
                    $path = str_replace('\\', '/', $path);
                    $record['path'] = str_replace('\\', '/', $record['path']);
                }

                $Storage->write($path, file_get_contents($file['tmp_name']), true);

                $data = $table->save($record, [
                    'validate' => false,
                    'callbacks' => false,
                ]);

                $operations = Configure::read('FileStorage.imageSizes.' . $record['model']);
                if (!empty($operations)) {
                    $this->_createVersions($table, $record, $operations);
                }
                $table->data = $data;
            } catch (\Exception $e) {
                $this->log($e->getMessage());
            }
        }
    }

    /**
     * Generates the path the image url / path for viewing it in a browser depending on the storage adapter
     *
     * @param \Cake\Event\Event $Event
     * @throws \Burzum\FileStorage\Storage\Listener\RuntimeException
     * @return void
     */
    public function imagePath(Event $Event): void
    {
        $data = $Event->getData();
        extract($data);

        if (!isset($data['image']['adapter'])) {
            throw new \RuntimeException(__d('file_storage', 'No adapter config key passed!'));
        }

        $adapterClass = $this->getAdapterClassName($data['image']['adapter']);
        $buildMethod = '_build' . $adapterClass . 'Path';

        if (method_exists($this, $buildMethod)) {
            $this->$buildMethod($Event);
        } else {
            $message = __d('file_storage', 'No callback image url callback implemented for adapter %s', $adapterClass);
            throw new \RuntimeException($message);
        }
    }

    /**
     * Builds an url to the given image
     *
     * @param \Cake\Event\Event $Event
     * @return void
     */
    protected function _buildLocalPath(Event $Event): void
    {
        $data = $Event->getData();
        extract($data);

        $path = $this->_buildPath($image, true, $hash);
        $Event->setResult('/' . $path);
        $Event->setData('path', '/' . $path);
        $Event->stopPropagation();
    }

    /**
     * Wrapper around the other AmazonS3 Adapter
     *
     * @param \Cake\Event\Event $Event
     * @see ImageProcessingListener::_buildAmazonS3Path()
     */
    protected function _buildAwsS3Path(\Cake\Event\Event $Event): void
    {
        $this->_buildAmazonS3Path($Event);
    }

    /**
     * Builds an url to the given image for the amazon s3 adapter
     * http(s)://<bucket>.s3.amazonaws.com/<object>
     * http(s)://s3.amazonaws.com/<bucket>/<object>
     *
     * @param \Cake\Event\Event $Event
     * @return void
     */
    protected function _buildAmazonS3Path(Event $Event): void
    {
        $data = $Event->getData();
        extract($data);

        $path = '/' . $this->_buildPath($image, true, $hash);

        $config = StorageManager::config($Event->getData('image.adapter'));
        $bucket = $config['adapterOptions'][1];
        if (!empty($config['cloudFrontUrl'])) {
            $cfDist = $config['cloudFrontUrl'];
        } else {
            $cfDist = null;
        }

        $http = 'http';
        $data = $Event->getData();
        if (!empty($data['options']['ssl']) && $data['options']['ssl'] === true) {
            $http = 'https';
        }

        $path = str_replace('\\', '/', $path);
        $bucketPrefix = !empty($data['options']['bucketPrefix']) && $data['options']['bucketPrefix'] === true;
        $Event->setResult($this->_buildCloudFrontDistributionUrl($http, $path, $bucket, $bucketPrefix, $cfDist));
        $Event->setData('path', $Event->getResult());
        $Event->stopPropagation();
    }

    /**
     * Builds an url to serve content from cloudfront
     *
     * @param string $protocol
     * @param string $image
     * @param string $bucket
     * @param string null $bucketPrefix
     * @param string $cfDist
     * @param bool $bucketPrefix
     * @return string
     */
    protected function _buildCloudFrontDistributionUrl(string $protocol, string $image, string $bucket, ?bool $bucketPrefix = null, ?string $cfDist = null): string
    {
        $path = $protocol . '://';
        if (is_string($cfDist)) {
            $path .= $cfDist;
        } else {
            if ($bucketPrefix) {
                $path .= $bucket . '.s3.amazonaws.com';
            } else {
                $path .= 's3.amazonaws.com/' . $bucket;
            }
        }
        $path .= $image;

        return $path;
    }

    /**
     * Builds a path to a file
     *
     * @param \Cake\Datasource\EntityInterface $record
     * @param bool $extension
     * @param string $hash
     * @return string
     */
    protected function _buildPath(EntityInterface $record, bool $extension = true, ?string $hash = null): string
    {
        if ($this->_config['pathBuilderOptions']['preserveFilename'] === true) {
            if (!empty($hash)) {
                $path = $record['path'] . preg_replace('/\.[^.]*$/', '', $record['filename']) . '.' . $hash . '.' . $record['extension'];
            } else {
                $path = $record['path'] . $record['filename'];
            }
        } else {
            $path = $record['path'] . str_replace('-', '', $record['id']);
            if (!empty($hash)) {
                $path .= '.' . $hash;
            }
            if ($extension === true) {
                $path .= '.' . $record['extension'];
            }
        }

        if ($this->adapterClass === 'AmazonS3' || $this->adapterClass === 'AwsS3') {
            return str_replace('\\', '/', $path);
        }

        return $path;
    }

    /**
     * Gets the adapter class name from the adapter configuration key
     *
     * @param string
     * @return string|false
     */
    public function getAdapterClassName(string $adapterConfigName)
    {
        $config = StorageManager::config($adapterConfigName);

        switch ($config['adapterClass']) {
            case '\Gaufrette\Adapter\Local':
                $this->adapterClass = 'Local';

                return $this->adapterClass;
            case '\Gaufrette\Adapter\AwsS3':
                $this->adapterClass = 'AwsS3';

                return $this->adapterClass;
            case '\Gaufrette\Adapter\AmazonS3':
                $this->adapterClass = 'AwsS3';

                return $this->adapterClass;
            case '\Gaufrette\Adapter\AwsS3':
                $this->adapterClass = 'AwsS3';

                return $this->adapterClass;
            default:
                return false;
        }
    }
}
