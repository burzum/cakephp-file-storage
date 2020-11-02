<?php
declare(strict_types=1);

namespace Burzum\FileStorage\Model\Behavior;

use ArrayAccess;
use Burzum\FileStorage\FileStorage\DataTransformer;
use Burzum\FileStorage\FileStorage\DataTransformerInterface;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\ORM\Behavior;
use Phauthentic\Infrastructure\Storage\FileInterface;
use Phauthentic\Infrastructure\Storage\FileStorage;
use Phauthentic\Infrastructure\Storage\Processor\ProcessorInterface;
use Phauthentic\Infrastructure\Storage\StorageServiceInterface;
use RuntimeException;
use Throwable;

/**
 * File Storage Behavior.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2020 Florian Krämer
 * @license MIT
 */
class FileStorageBehavior extends Behavior
{
    use EventDispatcherTrait;

    /**
     * @var \Phauthentic\Infrastructure\Storage\FileStorage
     */
    protected FileStorage $fileStorage;

    /**
     * @var \Phauthentic\Infrastructure\Storage\Processor\Image\ImageProcessor
     */
    protected ?ProcessorInterface $imageProcessor = null;

    /**
     * @var \Burzum\FileStorage\FileStorage\DataTransformerInterface
     */
    protected DataTransformerInterface $transformer;

    /**
     * Default config
     *
     * @var array
     */
    protected $_defaultConfig = [
        'defaultStorageConfig' => 'Local',
        'ignoreEmptyFile' => true,
        'fileField' => 'file',
        'fileStorage' => null,
        'imageProcessor' => null
    ];

    /**
     * @var array
     */
    protected array $processors = [];

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        if ($this->getConfig('fileStorage') instanceOf FileStorage) {
            $this->fileStorage = $this->getConfig('fileStorage');
        } else {
           throw new RuntimeException(
                'Missing or invalid fileStorage config key'
           );
        }

        if ($this->getConfig('imageProcessor') instanceOf ProcessorInterface) {
            $this->imageProcessor = $this->getConfig('imageProcessor');
        }

        if (!$this->getConfig('dataTransformer') instanceof DataTransformerInterface) {
            $this->transformer = new DataTransformer(
                $this->getTable()
            );
        }
    }

    /**
     * @throws \InvalidArgumentException
     * @param string $configName
     * @return array
     */
    public function getStorageAdapter($configName)
    {
        $this->fileStorage->getStorage($configName);
    }

    /**
     * Checks if a file upload is present.
     *
     * @param \Cake\Datasource\EntityInterface|array $entity
     * @return bool
     */
    protected function isFileUploadPresent($entity)
    {
        $field = $this->getConfig('fileField');
        if ($this->getConfig('ignoreEmptyFile') === true) {
            if (!isset($entity[$field]) || $entity[$field]->getError() === UPLOAD_ERR_NO_FILE) {
                return false;
            }
        }

        return true;
    }

    /**
     * beforeMarshal callback
     *
     * @param \Cake\Event\Event $event
     * @param \ArrayAccess $data
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayAccess $data)
    {
        if ($this->isFileUploadPresent($data)) {
            $this->getFileInfoFromUpload($data);
        }
    }

    /**
     * beforeSave callback
     *
     * @param \Cake\Event\Event $event
     * @param \Cake\Datasource\EntityInterface $entity
     * @return void
     */
    public function beforeSave(Event $event, EntityInterface $entity)
    {
        if (!$this->isFileUploadPresent($entity)) {
            return;
        }

        $this->checkEntityBeforeSave($entity);

        $this->dispatchEvent('FileStorage.beforeSave', [
            'entity' => $entity,
            'storageAdapter' => $this->getStorageAdapter($entity->get('adapter'))
        ], $this->getTable());
    }

    /**
     * afterSave callback
     *
     * @param \Cake\Event\Event $event
     * @param \Cake\Datasource\EntityInterface $entity
     * @param array $options
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity, $options)
    {
        if (!$this->isFileUploadPresent($entity)) {
            return;
        }

        if ($entity->isNew()) {
            try {
                $file = $this->entityToFileObject($entity);
                $file = $this->fileStorage->store($file);
                $file = $this->processImages($file);

                foreach ($this->processors as $processor) {
                    $file = $processor->process($file);
                }

                $entity = $this->fileObjectToEntity($file, $entity);
                $this->getTable()->save(
                    $entity,
                    ['callbacks' => false]
                );
            } catch (Throwable $exception) {
                $this->getTable()->delete($entity);
                throw $exception;
            }
        }

        $this->dispatchEvent('FileStorage.afterSave', [
            'entity' => $entity,
            'storageAdapter' => $this->getStorageAdapter($entity->get('adapter'))
        ], $this->getTable());

    }

    /**
     * checkEntityBeforeSave
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @return void
     */
    protected function checkEntityBeforeSave(EntityInterface $entity)
    {
        if ($entity->isNew()) {
            if (!$entity->has('model')) {
                $entity->set('model', $this->getTable()->getTable());
            }

            if (!$entity->has('adapter')) {
                $entity->set('adapter', $this->getConfig('defaultStorageConfig'));
            }
        }
    }

    /**
     * afterDelete callback
     *
     * @param \Cake\Event\Event $event
     * @param \Cake\Datasource\EntityInterface $entity
     * @param array $options
     * @return bool
     */
    public function afterDelete(Event $event, EntityInterface $entity, $options)
    {
        $this->dispatchEvent('FileStorage.afterDelete', [
            'entity' => $entity,
        ], $this->getTable());

        $file = $this->entityToFileObject($entity);
        $this->fileStorage->remove($file);
    }

    /**
     * Gets information about the file that is being uploaded.
     *
     * - gets the file size
     * - gets the mime type
     * - gets the extension if present
     *
     * @param array|\ArrayAccess $upload
     * @param string $field
     * @return void
     */
    protected function getFileInfoFromUpload(&$upload, $field = 'file')
    {
        /**
         * @var $uploadedFile \Psr\Http\Message\UploadedFileInterface
         */
        $uploadedFile = $upload[$field];

        $upload['filesize'] = $uploadedFile->getSize();
        $upload['mime_type'] = $uploadedFile->getClientMediaType();
        $upload['extension'] = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $upload['filename'] = $uploadedFile->getClientFilename();
    }

    /**
     * Don't use Table::deleteAll() if you don't want to end up with orphaned
     * files! The reason for that is that deleteAll() doesn't fire the
     * callbacks. So the events that will remove the files won't get fired.
     *
     * @param array $conditions Query::where() array structure.
     * @return int Number of deleted records / files
     */
    public function deleteAllFiles($conditions)
    {
        $table = $this->getTable();

        $results = $table->find()
            ->select((array)$table->getPrimaryKey())
            ->where($conditions)
            ->all();

        if ($results->count() > 0) {
            foreach ($results as $result) {
                $table->delete($result);
            }
        }

        return $results->count();
    }

    /**
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @return \Phauthentic\Infrastructure\Storage\FileInterface
     */
    public function entityToFileObject(EntityInterface $entity): FileInterface
    {
        return $this->transformer->entityToFileObject($entity);
    }

    /**
     * @param \Phauthentic\Infrastructure\Storage\FileInterface $file File
     * @param \Cake\Datasource\EntityInterface|null
     * @return \Cake\Datasource\EntityInterface
     */
    public function fileObjectToEntity(FileInterface $file, ?EntityInterface $entity)
    {
        return $this->transformer->fileObjectToEntity($file, $entity);
    }

    /**
     * Processes images
     *
     * @param \Phauthentic\Infrastructure\Storage\FileInterface $file File
     * @return \Phauthentic\Infrastructure\Storage\FileInterface
     */
    public function processImages(FileInterface $file): FileInterface
    {
        $imageSizes = Configure::read('FileStorage.imageVariants');
        $model = $file->model();

        if (!isset($imageSizes[$model])) {
            return $file;
        }

        $file = $file->withVariants($imageSizes[$model]);
        $file = $this->imageProcessor->process($file);

        return $file;
    }
}
