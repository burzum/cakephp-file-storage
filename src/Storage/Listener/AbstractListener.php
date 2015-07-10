<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

use Burzum\FileStorage\Storage\StorageTrait;
use Burzum\FileStorage\Storage\StorageUtils;
use Cake\Core\InstanceConfigTrait;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Table;
use Cake\Utility\MergeVariablesTrait;
use Cake\Utility\Text;
use Cake\Filesystem\Folder;

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
	use StorageTrait;
	use MergeVariablesTrait;

/**
 * The adapter class
 *
 * @param null|string
 */
	protected $_adapterClass = null;

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
		$this->_constructPathBuilder(
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
		$classes = [
			'\Burzum\FileStorage\Model\Table\FileStorageTable',
			'\Burzum\FileStorage\Model\Table\ImageStorageTable'
		];
		if (!in_array($this->storageTableClass, $classes)) {
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
			$this->log($e->getMessage(), 'file_storage');
			throw $e;
		}
	}

/**
 * Gets the hash of a file.
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
	public function getFileHash($file, $method = 'sha1') {
		return StorageUtils::getFileHash($file, $method);
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
 * Gets the configured path builder instance.
 *
 * @return \Burzum\FileStorage\Storage\PathBuilder\BasePathBuilder
 * @throws
	 */
	public function pathBuilder() {
		if (!empty($this->_pathBuilder)) {
			return $this->_pathBuilder;
		}
		throw \RuntimeException(sprintf('No path builder configured! Call _constructPathBuilder() and construct one!'));
	}

/**
 * Constructs a path builder instance.
 *
 * @param string $class
 * @param array $options
 * @return \Burzum\FileStorage\Storage\PathBuilder\BasePathBuilder
 * @throws \RuntimeException
 */
	public function _constructPathBuilder($class, array $options = []) {
		$classname = '\Burzum\FileStorage\Storage\PathBuilder\\' . $class . 'Builder';
		if (class_exists($classname)) {
			$this->_pathBuilder = new $classname($options);
			return $this->_pathBuilder;
		}
		$classname = '\App\Storage\PathBuilder\\' . $class . 'Builder';
		if (class_exists($classname)) {
			$this->_pathBuilder = new $classname($options);
		}
		$classname = $class;
		if (class_exists($classname)) {
			$this->_pathBuilder = new $classname($options);
		}
		if (empty($this->_pathBuilder)) {
			throw new \RuntimeException(sprintf('Could not find path builder "%s"!', $classname));
		}
		if ($this->_pathBuilder instanceof \Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface) {
			return $this->_pathBuilder;
		}
		throw new \RuntimeException(sprintf('Path builder class "%s" does not implement the PathBuilderInterface interface!'));
	}
}
