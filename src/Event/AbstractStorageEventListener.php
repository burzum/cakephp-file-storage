<?php
namespace FileStorage\Event;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Core\InstanceConfigTrait;
use FileStorage\Lib\StorageManager;
use FileStorage\Lib\Utility\FileStorageUtils;

abstract class AbstractStorageEventListener implements EventListenerInterface {

	use InstanceConfigTrait;

/**
 * The adapter class
 *
 * @param null|string
 */
	public $adapterClass = null;

/**
 * Name of the storage table class name the event listener requires the table
 * instances to extend
 *
 * Must be \FileStorage\Model\Table\FileStorageTable OR \FileStorage\Model\Table\ImageStorageTable
 *
 * @var string
 */
	public $storageTableClass = '\FileStorage\Model\Table\FileStorageTable';

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

	public function stripUuid($uuid) {
		return str_replace('-', '', $uuid);
	}

/**
 * Implemented Events
 *
 * @return array
 */
	abstract public function implementedEvents();

/**
 * Builds the filename of under which the data gets saved in the storage adapter
 *
 * @return string
 */
	public function buildFilename($table, $entity) {
		if ($this->_config['preserveFilename'] === true) {
			return $entity['filename'];
		}
		$filename = $entity['id'];
		if ($this->_config['stripUuid'] ===  true) {
			$filename = $this->stripUuid($filename);
		}
		if ($this->_config['preserveExtension'] === true) {
			$filename = $filename . '.' . $entity['extension'];
		}
		return $filename;
	}

/**
 * Builds the path under which the data gets stored in the storage adapter
 *
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
			$path .= $this->stripUuid($entity[$table->primaryKey()]) . DS;
		}
		return $path;
	}

/**
 * Check if the event is of a type or subject object of type model we want to
 * process with this listener
 *
 * @throws InvalidArgumentException
 * @param Event $event
 * @return boolean
 */
	protected function _checkEvent(Event $event) {
		if (!in_array($this->storageTableClass, array('\FileStorage\Model\Table\FileStorageTable', '\FileStorage\Model\Table\ImageStorageTable'))) {
			throw new InvalidArgumentException(__d('file_storage', 'Invalid storage model %s! Must be FileStorage or ImageStorage!', $this->storageTableClass));
		}
		return (
			$this->_checkTable($event)
			&& $this->getAdapterClassName($event->data['record']['adapter'])
			&& $this->_modelFilter($event)
		);
	}

/**
 * _modelFilter
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
 * Checks if the events subject is a model and extending FileStorage or ImageStorage
 *
 * @param Event $event
 * @return boolean
 */
	protected function _checkTable(Event $event) {
		$table = $event->subject();
		$instanceCheck = ($table instanceOf $this->storageTableClass);
		$adapterCheck = isset($event->data['record']['adapter']);
		return ($instanceCheck && $adapterCheck);
	}

/**
 * _getAdapterClassFromConfig
 *
 * @param string $configName
 * @return boolean|string
 */
	protected function _getAdapterClassFromConfig($configName) {
		$config = $this->getAdapterconfig($configName);
		if (!empty($config['adapterClass'])) {
			return $config['adapterClass'];
		}
		return false;
	}

/**
 * Gets the adapter class name from the adapter configuration key
 *
 * @param string
 * @return void
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
 * @param string $configName
 * @return array
 */
	public function getAdapterconfig($configName) {
		return StorageManager::config($configName);
	}

/**
 * Wrapper around the singleton call to StorageManager::config
 *
 * @param string $configName
 * @return Object
 */
	public function getAdapter($configName) {
		return StorageManager::adapter($configName);
	}

}

