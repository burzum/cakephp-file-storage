<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

use Burzum\FileStorage\Event\EventFilterTrait;
use Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait;
use Burzum\FileStorage\Storage\StorageException;
use Burzum\FileStorage\Storage\StorageTrait;
use Burzum\FileStorage\Storage\StorageUtils;
use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventListenerInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Table;
use Cake\Utility\MergeVariablesTrait;
use Psr\Log\LogLevel;

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
abstract class AbstractListener implements EventListenerInterface {

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
	 * @param null|string
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

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config = []) {
		$this->_mergeListenerVars();
		$this->config($config);
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
	protected function _mergeListenerVars() {
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
	 * @return void
	 */
	public function initialize($config) {}

	/**
	 * implementedEvents
	 *
	 * @return array
	 */
	public function implementedEvents() {
		return [
			'FileStorage.path' => 'getPath'
		];
	}

	public function addDataProcessor($objectName, $config) {
		if (is_array($config) && isset($config['className'])) {
			$name = $objectName;
			$objectName = $config['className'];
		} else {
			list(, $name) = pluginSplit($objectName);
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
		$this->eventManager()->on($dataProcessor);
	}

	/**
	 * Check if the event is of a type or subject object of type model we want to
	 * process with this listener.
	 *
	 * @param Event $event
	 * @return bool
	 * @throws \Burzum\FileStorage\Storage\StorageException
	 */
	protected function _checkEvent(Event $event) {
		$className = $this->_getAdapterClassFromConfig($event->data['entity']['adapter']);
		$classes = $this->_adapterClasses;
		if (!empty($classes) && !in_array($className, $classes)) {
			$message = 'The listener `%s` doesn\'t allow the `%s` adapter class! Probably because it can\'t work with it.';
			throw new StorageException(sprintf($message, get_class($this), $className));
		}

		return ($event->subject() instanceof Table && $this->_modelFilter($event));
	}

	public function _modelFilter() {
		return true;
	}

	/**
	 * Detects if an entities model field has name of one of the allowed models set.
	 *
	 * @param Event $event
	 * @return boolean
	 */
	protected function _identifierFilter(Event $event) {
		if (is_array($this->_config['identifiers'])) {
			$identifier = $event->data['entity']['model'];
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
	 * @return boolean|string False if the config is not present
	 */
	protected function _getAdapterClassFromConfig($configName) {
		$config = $this->storageConfig($configName);
		if (!empty($config['adapterClass'])) {
			return $config['adapterClass'];
		}
		return false;
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
	public function getAdapterClassName($configName) {
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
	 * @param Adapter $Storage Storage adapter
	 * @param string $path Path / key of the storage adapter file
	 * @param string $tmpFolder
	 * @throws \Burzum\FileStorage\Storage\StorageException
	 * @return string
	 */
	protected function _tmpFile($Storage, $path, $tmpFolder = null) {
		try {
			$tmpFile = $this->createTmpFile($tmpFolder);
			file_put_contents($tmpFile, $Storage->read($path));
			return $tmpFile;
		} catch (\Exception $e) {
			$this->log($e->getMessage());
			throw new StorageException('Failed to create the temporary file.', 0, $e);
		}
	}

	public function tmpFile($Storage, $path, $tmpFolder = null) {
		return $this->_tmpFile($Storage, $path, $tmpFolder);
	}

	/**
	 * Calculates the hash of a file.
	 *
	 * You can use this to compare if you got two times the same file uploaded.
	 *
	 * @param string $file Path to the file on your local machine.
	 * @param string $method 'md5' or 'sha1'
	 * @throws \InvalidArgumentException
	 * @link http://php.net/manual/en/function.md5-file.php
	 * @link http://php.net/manual/en/function.sha1-file.php
	 * @link http://php.net/manual/en/function.sha1-file.php#104748
	 * @return string
	 */
	public function calculateFileHash($file, $method = 'sha1') {
		return StorageUtils::getFileHash($file, $method);
	}

	/**
	 * Gets the hash for a file storage entity that is going to be stored.
	 *
	 * It first checks if hashing is enabled, if it is enabled it uses the the
	 * configured hashMethod to generate the hash and returns that hash.
	 *
	 * @param \Cake\Datasource\EntityInterface
	 * @param string $fileField
	 * @return null|string
	 */
	public function getFileHash(EntityInterface $entity, $fileField) {
		if ($this->config('fileHash') !== false) {
			return $this->calculateFileHash(
				$entity[$fileField]['tmp_name'],
				$this->config('fileHash')
			);
		}
		return null;
	}

	/**
	 * Creates a temporary file name and checks the tmp path, creates one if required.
	 *
	 * This method is thought to be used to generate tmp file locations for use cases
	 * like audio or image process were you need copies of a file and want to avoid
	 * conflicts. By default the tmp file is generated using cakes TMP constant +
	 * folder if passed and a uuid as filename.
	 *
	 * @param string $folder
	 * @param boolean $checkAndCreatePath
	 * @return string For example /var/www/app/tmp/<uuid> or /var/www/app/tmp/<my-folder>/<uuid>
	 */
	public function createTmpFile($folder = null, $checkAndCreatePath = true) {
		return StorageUtils::createTmpFile($folder, $checkAndCreatePath);
	}

	/**
	 * Get the path for a storage entity.
	 *
	 * @param \Cake\Event\Event $event
	 * @return string
	 */
	public function getPath(Event $event) {
		$pathBuilder = $this->pathBuilder();
		$method = $event->data['method'];
		if (!method_exists($pathBuilder, $event->data['method'])) {
			throw new \BadMethodCallException(sprintf('`%s` does not implement the `%s` method!', get_class($pathBuilder), $method));
		}
;
		$event = $this->dispatchEvent('FileStorage.beforeGetPath', [
			'entity' => $event->data['entity'],
			'storageAdapter' => $this->getStorageAdapter($event->data['entity']['adapter']),
			'pathBuilder' => $pathBuilder
		]);

		if ($event->isStopped()) {
			return $event->result;
		}

		if ($event->subject() instanceof EntityInterface) {
			$event->data['entity'];
		}
		if (empty($event->data['entity'])) {
			throw new \RuntimeException('No entity present!');
		}

		$path = $pathBuilder->{$method}($event->data['entity'], $event->data);

		$event = $this->dispatchEvent('FileStorage.afterGetPath', [
			'entity' => $event->data['entity'],
			'storageAdapter' => $this->getStorageAdapter($event->data['entity']['adapter']),
			'pathBuilder' => $pathBuilder,
			'path' => $path
		]);

		if ($event->isStopped()) {
			return $event->result;
		}

		return $path;
	}

	/**
	 * Stores the file in the configured storage backend.
	 *
	 * @param \Cake\Event\Event $event
	 * @return bool
	 * @throws \Burzum\FileStorage\Storage\StorageException
	 */
	protected function _storeFile(Event $event) {
		try {
			$beforeEvent = $this->_beforeStoreFile($event);
			if ($beforeEvent->isStopped()) {
				return $beforeEvent->result;
			}

			$fileField = $this->config('fileField');
			$entity = $event->data['entity'];
			$Storage = $this->getStorageAdapter($entity['adapter']);
			$Storage->write($entity['path'], file_get_contents($entity[$fileField]['tmp_name']), true);

			$event->result = $event->subject()->save($entity, array(
				'checkRules' => false
			));

			$this->_afterStoreFile($event);
			if ($event->isStopped()) {
				return $event->result;
			}

			return true;
		} catch (\Exception $e) {
			$this->log($e->getMessage(), LogLevel::ERROR, ['scope' => ['storage']]);
			throw new StorageException($e->getMessage(), $e->getCode(), $e);
		}

		return false;
	}

	/**
	 * Deletes the file from the configured storage backend.
	 *
	 * @param \Cake\Event\Event $event
	 * @return bool
	 * @throws \Burzum\FileStorage\Storage\StorageException
	 */
	protected function _deleteFile(Event $event) {
		try {
			$this->_beforeDeleteFile($event);
			$entity = $event->data['entity'];
			$path = $this->pathBuilder()->fullPath($entity);

			if ($this->getStorageAdapter($entity->adapter)->delete($path)) {
				$event->result = true;
				$event->data['path'] = $path;
				$event->data['entity'] = $entity;
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
	 * @param \Cake\Event\Event $event
	 * @return \Cake\Event\Event
	 */
	protected function _beforeStoreFile(Event $event) {
		return $this->dispatchEvent('FileStorage.beforeStoreFile', [
			'entity' => $event->data['entity'],
			'adapter' => $this->getStorageAdapter($event->data['entity']['adapter'])
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
	 * @param \Cake\Event\Event $event
	 * @return \Cake\Event\Event
	 */
	protected function _afterStoreFile(Event $event) {
		return $this->dispatchEvent('FileStorage.afterStoreFile', [
			'entity' => $event->data['entity'],
			'adapter' => $this->getStorageAdapter($event->data['entity']['adapter'])
		]);
	}

	/**
	 * Callback to handle the case something needs to be done before the file is
	 * deleted from the storage backend.
	 *
	 * By default this will trigger an event FileStorage.afterStoreFile but you
	 * can also just overload this method.
	 *
	 * @param \Cake\Event\Event $event
	 * @return \Cake\Event\Event
	 */
	protected function _beforeDeleteFile(Event $event) {
		return $this->dispatchEvent('FileStorage.beforeDeleteFile', [
			'entity' => $event->data['entity'],
			'adapter' => $this->getStorageAdapter($event->data['entity']['adapter'])
		]);
	}

	/**
	 * Callback to handle the case something needs to be done after the file was
	 * successfully removed from the storage backend.
	 *
	 * By default this will trigger an event FileStorage.afterStoreFile but you
	 * can also just overload this method.
	 *
	 * @param \Cake\Event\Event $event
	 * @return \Cake\Event\Event
	 */
	protected function _afterDeleteFile(Event $event) {
		return $this->dispatchEvent('FileStorage.afterDeleteFile', [
			'entity' => $event->data['entity'],
			'adapter' => $this->getStorageAdapter($event->data['entity']['adapter'])
		]);
	}
}
