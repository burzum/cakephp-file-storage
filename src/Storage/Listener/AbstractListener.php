<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

use Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait;
use Burzum\FileStorage\Storage\StorageException;
use Burzum\FileStorage\Storage\StorageTrait;
use Burzum\FileStorage\Storage\StorageUtils;
use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Event\EventManager;
use Cake\Filesystem\Folder;
use Cake\Log\LogTrait;
use Cake\ORM\Table;
use Cake\Utility\MergeVariablesTrait;
use Cake\Utility\Text;
use Psr\Log\LogLevel;

/**
 * AbstractListener
 *
 * Filters events and entities to decide if they should be processed or not by
 * a specific storage adapter.
 *
 * - Filter by table base class name
 * - Filter by the entities model field
 * - Filter by adapter class
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

	/**
	 * Check if the event is of a type or subject object of type model we want to
	 * process with this listener.
	 *
	 * @param Event $event
	 * @return bool
	 * @throws \Burzum\FileStorage\Storage\StorageException
	 */
	protected function _checkEvent(Event $event) {
		$className = $this->_getAdapterClassFromConfig($event->data['record']['adapter']);
		$classes = $this->_adapterClasses;
		if (!empty($classes) && !in_array($className, $this->_adapterClasses)) {
			$message = 'The listener `%s` doesn\'t allow the `%s` adapter class! Probably because it can\'t work with it.';
			throw new StorageException(sprintf($message, get_class($this), $className));
		}
		return (
			isset($event->data['table'])
			&& $event->data['table'] instanceof Table
			&& $this->_modelFilter($event)
		);
	}

	/**
	 * Detects if an entities model field has name of one of the allowed models set.
	 *
	 * @param Event $event
	 * @return boolean
	 */
	protected function _modelFilter(Event $event) {
		if (is_array($this->_config['models'])) {
			$model = $event->data['record']['model'];
			if (!in_array($model, $this->_config['models'])) {
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
	 * @throws \Exception
	 * @return string
	 */
	protected function _tmpFile($Storage, $path, $tmpFolder = null) {
		try {
			$tmpFile = $this->createTmpFile($tmpFolder);
			file_put_contents($tmpFile, $Storage->read($path));
			return $tmpFile;
		} catch (\Exception $e) {
			$this->log($e->getMessage());
			throw $e;
		}
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
		if (is_null($folder)) {
			$folder = TMP;
		}
		if ($checkAndCreatePath === true && !is_dir($folder)) {
			new Folder($folder, true);
		}
		return $folder . Text::uuid();
	}

	/**
	 * Get the path for a storage entity.
	 *
	 * @param \Cake\Event\Event $event
	 * @return string
	 */
	public function getPath(Event $event) {
		return $this->pathBuilder()->{$event->data['method']}($event->subject(), $event->data);
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
			$this->_handleLegacyEvent($event);
			$fileField = $this->config('fileField');
			$entity = $event->data['entity'];
			$Storage = $this->storageAdapter($entity['adapter']);
			$Storage->write($entity['path'], file_get_contents($entity[$fileField]['tmp_name']), true);
			$event->result = $event->data['table']->save($entity, array(
				'checkRules' => false
			));
			$this->_afterStoreFile($event);
			return true;
		} catch (\Exception $e) {
			throw $e;
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
			$this->_handleLegacyEvent($event);
			$entity = $event->data['entity'];
			$path = $this->pathBuilder()->fullPath($entity);
			if ($this->storageAdapter($entity->adapter)->delete($path)) {
				if ($this->_config['imageProcessing'] === true) {
					$this->autoProcessImageVersions($entity, 'remove');
				}
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
	 * @param \Cake\Event\Event $event;
	 * @return void
	 */
	protected function _afterStoreFile(Event $event) {
		$this->_handleLegacyEvent($event);
		$afterStoreEvent = new Event('FileStorage.afterStoreFile', $this, [
			'entity' => $event->result,
			'adapter' => $this->storageAdapter($event->result['adapter'])
		]);
		EventManager::instance()->dispatch($afterStoreEvent);
	}

	/**
	 * Callback to handle the case something needs to be done after the file was
	 * successfully removed from the storage backend.
	 *
	 * @param \Cake\Event\Event $event;
	 * @return void
	 */
	protected function _afterDeleteFile(Event $event) {
		$this->_handleLegacyEvent($event);
		$afterDeleteEvent = new Event('FileStorage.afterDeleteFile', $this, [
			'entity' => $event->data['entity'],
			'adapter' => $this->storageAdapter($event->data['entity']->adapter)
		]);
		EventManager::instance()->dispatch($afterDeleteEvent);
	}

	/**
	 * Handles legacy events
	 *
	 * - Copies the old 'record' data to 'entity'
	 *
	 * @param \Cake\Event\Event
	 * @return void
	 */
	protected function _handleLegacyEvent(Event &$event) {
		if (isset($event->data['record'])) {
			$event->data['entity'] = $event->data['record'];
		}
	}
}
