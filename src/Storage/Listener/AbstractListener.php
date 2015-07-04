<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

use Burzum\FileStorage\Lib\StorageManager;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\Utility\Text;
use Cake\Filesystem\Folder;

/**
 * AbstractListener
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
abstract class AbstractListener implements EventListenerInterface {

	use InstanceConfigTrait;

/**
 * The adapter class
 *
 * @param null|string
 */
	public $adapterClass = null;

/**
 * The class used to generate path and file names.
 *
 * @param string
 */
	protected $_pathBuilder = null;

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
 * @return AbstractListener
 */
	public function __construct(array $config = []) {
		$this->config($config);
		$this->initialize();
	}

/**
 * Helper method to bypass the need to override the constructor.
 *
 * Called last inside __construct()
 *
 * @return void
 */
	public function initialize() {}

/**
 * Implemented Events
 *
 * @return array
 */
	abstract public function implementedEvents();

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
			$this->log($e->getMessage(), 'file_storage');
			throw $e;
		}
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
 * Path builder.
 *
 * @param string Class name of a path builder.
 * @param array Options for the path builder.
 * @return \Burzum\FileStorage\Storage\PathBuilder\BasePathBuilder
 */
	public function pathBuilder($class = null, array $options = []) {
		if (empty($class)) {
			if (empty($this->_pathBuilder)) {
				throw new \RuntimeException(sprintf('No path builder loaded!'));
			}
			return $this->_pathBuilder;
		}
		$classname = '\Burzum\FileStorage\Storage\PathBuilder\\' . $class . 'Builder';
		if (class_exists($classname)) {
			$this->_pathBuilder = new $classname();
			return $this->_pathBuilder;
		}
		$classname = '\App\Storage\PathBuilder\\' . $class . 'Builder';
		if (class_exists($classname)) {
			$this->_pathBuilder = new $classname();
			return $this->_pathBuilder;
		}
		$classname = $class;
		if (class_exists($classname)) {
			$this->_pathBuilder = new $classname();
			return $this->_pathBuilder;
		}
		throw new \RuntimeException(sprintf('Could not find path builder %s!', $classname));
	}
}
