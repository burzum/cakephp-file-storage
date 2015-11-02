<?php
namespace Burzum\FileStorage\Event;

use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\Utility\Text;
use Cake\Filesystem\Folder;
use Burzum\FileStorage\Lib\StorageManager;
use Burzum\FileStorage\Lib\FileStorageUtils;

/**
 * AbstractStorageEventListener
 *
 * Filters events and entities to decide if they should be processed or not by
 * a specific adapter.
 *
 * - Filter by table base class name
 * - Filter by the entities model field
 * - Filter by adapter class
 *
 * Provides basic functionality to build
 *
 * - filename
 * - path
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
abstract class AbstractStorageEventListener implements EventListenerInterface {

	use InstanceConfigTrait;
	use LogTrait;

/**
 * The adapter class
 *
 * @param null|string
 */
	public $adapterClass = null;

/**
 * Name of the storage table class name the event listener requires the table
 * instances to extend.
 *
 * This information is important to know when to use the event callbacks or not.
 *
 * Must be \FileStorage\Model\Table\FileStorageTable or \FileStorage\Model\Table\ImageStorageTable
 *
 * @var string
 */
	public $storageTableClass = '\Burzum\FileStorage\Model\Table\FileStorageTable';

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
	protected $_adapterClasses = array();

/**
 * Default settings
 *
 * @var array
 */
	protected $_defaultConfig = array(
		'models' => false,
		'stripUuid' => true,
		'preserveFilename' => false,
		'preserveExtension' => true,
		'uuidFolder' => true,
		'randomPath' => true,
		'tableFolder' => false
	);

/**
 * Constructor
 *
 * @param array $config
 * @return AbstractStorageEventListener
 */
	public function __construct(array $config = []) {
		$this->config($config);
	}

/**
 * Strips dashes from a string
 *
 * @param string
 * @return string String without the dashed
 */
	public function stripDashes($uuid) {
		return str_replace('-', '', $uuid);
	}

/**
 * Implemented Events
 *
 * @return array
 */
	abstract public function implementedEvents();

/**
 * Builds the filename of under which the data gets saved in the storage adapter.
 *
 * @param Table $table
 * @param Entity $entity
 * @return string
 */
	public function buildFilename($table, $entity) {
		if ($this->_config['preserveFilename'] === true) {
			return $entity['filename'];
		}
		$filename = $entity['id'];
		if ($this->_config['stripUuid'] ===  true) {
			$filename = $this->stripDashes($filename);
		}
		if ($this->_config['preserveExtension'] === true) {
			$filename = $filename . '.' . $entity['extension'];
		}
		return $filename;
	}

/**
 * Builds the path under which the data gets stored in the storage adapter.
 *
 * @param Table $table
 * @param Entity $entity
 * @return string
 */
	public function buildPath($table, $entity) {
		$path = '';
		if ($this->_config['tableFolder']) {
			$path .= $table->table() . DS;
		}
		if ($this->_config['randomPath'] == true) {
			$path .= FileStorageUtils::randomPath($entity[$table->primaryKey()]);
		}
		if ($this->_config['uuidFolder'] == true) {
			$path .= $this->stripDashes($entity[$table->primaryKey()]) . DS;
		}
		return $path;
	}

/**
 * Check if the event is of a type or subject object of type model we want to
 * process with this listener.
 *
 * @throws \InvalidArgumentException
 * @param Event $event
 * @return boolean
 */
	protected function _checkEvent(Event $event) {
		if (!in_array($this->storageTableClass, array('\Burzum\FileStorage\Model\Table\FileStorageTable', '\Burzum\FileStorage\Model\Table\ImageStorageTable'))) {
			throw new \InvalidArgumentException(sprintf('Invalid storage table `%s`! Table must be FileStorage or ImageStorage or extend one of both!', $this->storageTableClass));
		}
		return (
			$this->_checkTable($event)
			&& $this->getAdapterClassName($event->data['record']['adapter'])
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
 * Checks if the events subject is a model and extending FileStorage or ImageStorage.
 *
 * @param Event $event
 * @return boolean
 */
	protected function _checkTable(Event $event) {
		return ($event->subject() instanceOf $this->storageTableClass);
	}

/**
 * Gets the adapter class name from the adapter config
 *
 * @param string $configName Name of the configuration
 * @return boolean|string False if the config is not present
 */
	protected function _getAdapterClassFromConfig($configName) {
		$config = $this->getAdapterconfig($configName);
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
 * @return boolean|string String, the adapter class name or false if it was not found.
 */
	public function getAdapterClassName($configName) {
		$className = $this->_getAdapterClassFromConfig($configName);
		if (in_array($className, $this->_adapterClasses)) {
			$position = strripos($className, '\\');
			$this->adapterClass = substr($className, $position + 1, strlen($className));
			return $this->adapterClass;
		}
		return false;
	}

/**
 * Wrapper around the singleton call to StorageManager::config
 *
 * Makes it easy to mock the adapter in tests.
 *
 * @param string $configName
 * @return array
 */
	public function getAdapterconfig($configName) {
		return StorageManager::config($configName);
	}

/**
 * Wrapper around the singleton call to StorageManager::config
 *
 * Makes it easy to mock the adapter in tests.
 *
 * @param string $configName
 * @return Object
 */
	public function getAdapter($configName) {
		return StorageManager::adapter($configName);
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
 * @throws Exception
 * @return bool|string
 */
	protected function _tmpFile($Storage, $path, $tmpFolder = null) {
		try {
			$tmpFile = $this->createTmpFile($tmpFolder);
			file_put_contents($tmpFile, $Storage->read($path));
			return $tmpFile;
		} catch (Exception $e) {
			$this->log($e->getMessage());
			throw $e;
		}
	}

/**
 * Creates a temporary file name and checks the tmp path, creates one if required
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
		return $folder . DS . Text::uuid();
	}

/**
 * Generates a semi-random file system path
 *
 * @deprecated Don't use this anymore but it is still used by two listeners :(
 * @param string $type
 * @param string $string
 * @param boolean $idFolder
 * @return string
 */
	public function fsPath($type, $string, $idFolder = true) {
		$string = str_replace('-', '', $string);
		$path = $type . DS . FileStorageUtils::randomPath($string);
		if ($idFolder) {
			$path .= $string . DS;
		}
		return $path;
	}

}
