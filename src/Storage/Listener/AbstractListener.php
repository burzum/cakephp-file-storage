<?php
declare(strict_types=1);
/**
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

use BadMethodCallException;
use Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait;
use Burzum\FileStorage\Storage\StorageException;
use Burzum\FileStorage\Storage\StorageTrait;
use Burzum\FileStorage\Storage\StorageUtils;
use Cake\Core\App;
use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Table;
use Cake\Utility\MergeVariablesTrait;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * AbstractListener
 *
 * These abstracted features are provided by the class as well:
 *
 * - Provides access to the path builders to build file names and paths.
 * - Provides access to the storage adapters.
 *
 * All of this in combination allows you to build event listeners to handle the
 * storage of files in any place and storage backend very well and in a clean
 * abstracted way.
 */
abstract class AbstractListener implements EventListenerInterface
{
    use EventDispatcherTrait;
    use EventFilterTrait;
    use InstanceConfigTrait;
    use LogTrait;
    use MergeVariablesTrait;
    use PathBuilderTrait;
    use StorageTrait;

    /**
     * The adapter class
     *
     * @var null|string
     */
    protected $_adapterClass = null;

    /**
     * List of adapter classes the event listener can work with
     *
     * It is used in FileStorageEventListenerBase::getAdapterClassName to get the
     * class, to detect if an event passed to this listener should be processed or
     * not. Only events with an adapter class present in this array will be
     * processed.
     *
     * @var array
     */
    protected $_adapterClasses = [];

    /**
     * Default settings
     *
     * @var array
     */
    protected $_defaultConfig = [
        'pathBuilder' => '',
        'pathBuilderOptions' => [],
        'fileHash' => 'sha1',
        'fileField' => 'file',
        'models' => false,
    ];

    protected $_loaded;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->_mergeListenerVars();
        $this->setConfig($config);
        $this->pathBuilder(
            $this->_config['pathBuilder'],
            $this->_config['pathBuilderOptions']
        );
        $this->initialize($config);
    }

    /**
     * Merges properties.
     *
     * @return void
     */
    protected function _mergeListenerVars(): void
    {
        $this->_mergeVars(
            ['_defaultConfig'],
            ['associative' => ['_defaultConfig']]
        );
    }

    /**
     * Helper method to bypass the need to override the constructor.
     *
     * Called last inside __construct()
     *
     * @param array $config
     * @return void
     */
    public function initialize(array $config): void
    {
    }

    /**
     * implementedEvents
     *
     * @return array
     */
    public function implementedEvents(): array
    {
        return [
            'FileStorage.path' => 'getPath',
        ];
    }

    public function addDataProcessor($objectName, $config)
    {
        if (is_array($config) && isset($config['className'])) {
            $name = $objectName;
            $objectName = $config['className'];
        } else {
            [, $name] = pluginSplit($objectName);
        }

        $loaded = isset($this->_loaded[$name]);
        if ($loaded && !empty($config)) {
            $this->_checkDuplicate($name, $config);
        }
        if ($loaded) {
            return $this->_loaded[$name];
        }

        $className = App::className($objectName, 'Storage/DataProcessor', 'Processor');
        $dataProcessor = new $className($config);
        $this->getEventManager()->on($dataProcessor);
    }

    /**
     * Check if the event is of a type or subject object of type model we want to
     * process with this listener.
     *
     * @param \Cake\Event\EventInterface $event
     * @return bool
     * @throws \Burzum\FileStorage\Storage\StorageException
     */
    protected function _checkEvent(EventInterface $event): bool
    {
        $className = $this->_getAdapterClassFromConfig($event->getData('entity')['adapter']);
        $classes = $this->_adapterClasses;

        if (!empty($classes) && !in_array($className, $classes)) {
            $message = 'The listener `%s` doesn\'t allow the `%s` adapter class! Probably because it can\'t work with it.';
            throw new StorageException(sprintf(
                $message,
                static::class,
                $className
            ));
        }

        return $event->getSubject() instanceof Table && $this->_modelFilter($event);
    }

    public function _modelFilter(EventInterface $event): bool
    {
        return true;
    }

    /**
     * Detects if an entities model field has name of one of the allowed models set.
     *
     * @param \Cake\Event\Event $event
     * @return bool
     */
    protected function _identifierFilter(Event $event): bool
    {
        if (is_array($this->_config['identifiers'])) {
            $identifier = $event->getData('entity.model');
            if (!in_array($identifier, $this->_config['identifiers'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets the adapter class name from the adapter config
     *
     * @param string $configName Name of the configuration
     * @return null|string False if the config is not present
     */
    protected function _getAdapterClassFromConfig(string $configName): ?string
    {
        $config = $this->getStorageConfig($configName);

        if (!empty($config['adapterClass'])) {
            return $config['adapterClass'];
        }

        return null;
    }

    /**
     * Gets the adapter class name from the adapter configuration key and checks if
     * it is in the list of supported adapters for the listener.
     *
     * You must define a list of supported classes via AbstractStorageEventListener::$_adapterClasses.
     *
     * @param string $configName Name of the adapter configuration.
     * @return string|false String, the adapter class name or false if it was not found.
     */
    public function getAdapterClassName(string $configName)
    {
        $className = $this->_getAdapterClassFromConfig($configName);
        if (in_array($className, $this->_adapterClasses)) {
            $position = strripos($className, '\\');
            $this->_adapterClass = substr($className, $position + 1, strlen($className));

            return $this->_adapterClass;
        }

        return false;
    }

    /**
     * Create a temporary file locally based on a file from an adapter.
     *
     * A common case is image manipulation or video processing for example. It is
     * required to get the file first from the adapter and then write it to
     * a tmp file. Then manipulate it and upload the changed file.
     *
     * The adapter might not be one that is using a local file system, so we first
     * get the file from the storage system, store it locally in a tmp file and
     * later load the new file that was generated based on the tmp file into the
     * storage adapter. This method here just generates the tmp file.
     *
     * @param mixed $storage Storage adapter @todo fix type
     * @param string $path Path / key of the storage adapter file
     * @param string|null $tmpFolder
     * @throws \Burzum\FileStorage\Storage\StorageException
     * @return string
     */
    protected function _tmpFile($storage, string $path, ?string $tmpFolder = null): string
    {
        try {
            $tmpFile = $this->createTmpFile($tmpFolder);
            file_put_contents($tmpFile, $storage->read($path));

            return $tmpFile;
        } catch (\Exception $e) {
            $this->log($e->getMessage());
            throw new StorageException('Failed to create the temporary file.', 0, $e);
        }
    }

    /**
     * @param mixed $storage Storage adapter @todo fix type
     * @param string $path Path / key of the storage adapter file
     * @param string|null $tmpFolder
     * @throws \Burzum\FileStorage\Storage\StorageException
     * @return string
     */
    public function tmpFile($storage, string $path, ?string $tmpFolder = null): string
    {
        return $this->_tmpFile($storage, $path, $tmpFolder);
    }

    /**
     * Creates a temporary file name and checks the tmp path, creates one if required.
     *
     * This method is thought to be used to generate tmp file locations for use cases
     * like audio or image process were you need copies of a file and want to avoid
     * conflicts. By default the tmp file is generated using cakes TMP constant +
     * folder if passed and a uuid as filename.
     *
     * @param string|null $folder
     * @param bool|bool $checkAndCreatePath
     * @return string For example /var/www/app/tmp/<uuid> or /var/www/app/tmp/<my-folder>/<uuid>
     */
    public function createTmpFile(?string $folder = null, bool $checkAndCreatePath = true): string
    {
        return StorageUtils::createTmpFile($folder, $checkAndCreatePath);
    }

    /**
     * Get the path for a storage entity.
     *
     * @param \Cake\Event\Event $event
     * @return string
     */
    public function getPath(Event $event): string
    {
        $pathBuilder = $this->pathBuilder();
        $method = $event->getData('method');
        if (!method_exists($pathBuilder, $event->getData('method'))) {
            throw new BadMethodCallException(sprintf('`%s` does not implement the `%s` method!', get_class($pathBuilder), $method));
        }

        $event = $this->dispatchEvent('FileStorage.beforeGetPath', [
            'entity' => $event->getData('entity'),
            'storageAdapter' => $this->getStorageAdapter($event->getData('entity')->get('adapter')),
            'pathBuilder' => $pathBuilder,
        ]);

        if ($event->isStopped()) {
            return $event->getResult();
        }

        if ($event->getSubject() instanceof EntityInterface) {
            $event->getData('entity');
        }

        if (empty($event->getData('entity'))) {
            throw new RuntimeException('No entity present!');
        }

        $path = $pathBuilder->{$method}($event->getData('entity'), $event->getData());

        $entity = $event->getData('entity');
        $event = $this->dispatchEvent('FileStorage.afterGetPath', [
            'entity' => $entity,
            'storageAdapter' => $this->getStorageAdapter($entity->get('adapter')),
            'pathBuilder' => $pathBuilder,
            'path' => $path,
        ]);

        if ($event->isStopped()) {
            return $event->getResult();
        }

        return $path;
    }

    /**
     * Stores the file in the configured storage backend.
     *
     * @param \Cake\Event\EventInterface $event
     * @return bool
     * @throws \Burzum\FileStorage\Storage\StorageException
     */
    protected function _storeFile(EventInterface $event): bool
    {
        try {
            $beforeEvent = $this->_beforeStoreFile($event);
            if ($beforeEvent->isStopped()) {
                return $beforeEvent->getResult();
            }

            $fileField = $this->getConfig('fileField');
            $entity = $event->getData('entity');
            $Storage = $this->getStorageAdapter($entity['adapter']);
            $Storage->write($entity['path'], file_get_contents($entity[$fileField]['tmp_name']), true);

            $event->setResult($event->getSubject()
                    ->save($entity, [
                        'checkRules' => false,
                    ]));

            $this->_afterStoreFile($event);
            if ($event->isStopped()) {
                return $event->getResult();
            }

            return true;
        } catch (\Exception $e) {
            $this->log($e->getMessage(), LogLevel::ERROR, ['scope' => ['storage']]);
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Deletes the file from the configured storage backend.
     *
     * @param \Cake\Event\EventInterface $event
     * @return bool
     * @throws \Burzum\FileStorage\Storage\StorageException
     */
    protected function _deleteFile(EventInterface $event): bool
    {
        try {
            $this->_beforeDeleteFile($event);
            $entity = $event->getData('entity');
            $path = $this->pathBuilder()->fullPath($entity);

            if ($this->getStorageAdapter($entity->adapter)->delete($path)) {
                $event->setResult(true);
                $event->setData('path', $path);
                $event->setData('entity', $entity);
                $this->_afterDeleteFile($event);

                return true;
            }
        } catch (\Exception $e) {
            $this->log($e->getMessage(), LogLevel::ERROR, ['scope' => ['storage']]);
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }

        return false;
    }

    /**
     * Creates and triggers the FileStorage.beforeStoreFile event.
     *
     * @param \Cake\Event\EventInterface $event
     * @return \Cake\Event\EventInterface
     */
    protected function _beforeStoreFile(EventInterface $event): EventInterface
    {
        $entity = $event->getData('entity');

        return $this->dispatchEvent('FileStorage.beforeStoreFile', [
            'entity' => $entity,
            'adapter' => $this->getStorageAdapter($entity->get('adapter')),
        ]);
    }

    /**
     * Callback to handle the case something needs to be done after the file was
     * successfully stored in the storage backend.
     *
     * By default this will trigger an event FileStorage.afterStoreFile but you
     * can also just overload this method and implement your own logic here.
     *
     * This method is a good place to flag a file for some post processing or
     * directly doing the post processing like image versions or
     * video compression.
     *
     * @param \Cake\Event\EventInterface $event
     * @return \Cake\Event\EventInterface
     */
    protected function _afterStoreFile(EventInterface $event): EventInterface
    {
        $entity = $event->getData('entity');

        return $this->dispatchEvent('FileStorage.afterStoreFile', [
            'entity' => $event->getData('entity'),
            'adapter' => $this->getStorageAdapter($entity->get('adapter')),
        ]);
    }

    /**
     * Callback to handle the case something needs to be done before the file is
     * deleted from the storage backend.
     *
     * By default this will trigger an event FileStorage.afterStoreFile but you
     * can also just overload this method.
     *
     * @param \Cake\Event\EventInterface $event
     * @return \Cake\Event\EventInterface
     */
    protected function _beforeDeleteFile(EventInterface $event): EventInterface
    {
        $entity = $event->getData('entity');

        return $this->dispatchEvent('FileStorage.beforeDeleteFile', [
            'entity' => $entity,
            'adapter' => $this->getStorageAdapter($entity->get('adapter')),
        ]);
    }

    /**
     * Callback to handle the case something needs to be done after the file was
     * successfully removed from the storage backend.
     *
     * By default this will trigger an event FileStorage.afterStoreFile but you
     * can also just overload this method.
     *
     * @param \Cake\Event\EventInterface $event
     * @return \Cake\Event\EventInterface
     */
    protected function _afterDeleteFile(EventInterface $event): EventInterface
    {
        $entity = $event->getData('entity');

        return $this->dispatchEvent('FileStorage.afterDeleteFile', [
            'entity' => $entity,
            'adapter' => $this->getStorageAdapter($entity->get('adapter')),
        ]);
    }
}
