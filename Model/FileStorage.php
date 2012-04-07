<?php
App::uses('Folder', 'Utility');
App::uses('FileStorageAppModel', 'FileStorage.Model');
/**
 * FileStorage
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class FileStorage extends FileStorageAppModel {
/**
 * Name
 *
 * @var string
 */
	public $name = 'FileStorage';

/**
 * Table name
 *
 * @var string
 */
	public $useTable = 'file_storage';

/**
 * Displayfield
 *
 * @var string
 */
	public $displayField = 'filename';

/**
 * Adapters
 *
 * @var array
 */
	public $adapters = array(
		'Local' => array(
			'adapterOptions' => array(TMP, true),
			'adapterClass' => '\Gaufrette\Adapter\Local',
			'class' => '\Gaufrette\Filesystem'));

/**
 * 
 */
	public $activeAdapter = 'Local';

/**
 * Constructor
 *
 * @param 
 * @param 
 * @param 
 * @return void
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$adapterConfig = Configure::read('FileStorage.adapters');
		if (is_array($adapterConfig)) {
			$this->adapters = array_merge($this->adapters, $adapterConfig);
		}
	}

/**
 * Sets or gets the active storage adapter
 *
 * @param string
 * @return mixed
 */
	public function adapter($adapter = null) {
		if (empty($adapter)) {
			return $this->activeAdapter;
		}
		if (isset($this->adapters[$adapter])) {
			return $this->activeAdapter = $adapter;
		}
		return  false;
	}

/**
 * Renews the FileUpload behavior with a new configuration
 *
 * @param array $options
 * @return void
 */
	public function configureFileUpload($options = array()) {
		$this->Behaviors->unload('FileUpload');
		$this->Behaviors->load('FileUpload', $options);
	}

/**
 * beforeSave callback
 *
 * @return boolean true on success
 */
	public function beforeSave($options) {
		if (!empty($this->data[$this->alias]['file']['tmp_name'])) {
			$File = new File($this->data[$this->alias]['file']['tmp_name']);
			$this->data[$this->alias]['filesize'] = $File->size();
			$this->data[$this->alias]['mime_type'] = $File->mime();
		}
		if (!empty($this->data[$this->alias]['file']['name'])) {
			$this->data[$this->alias]['extension'] = $this->fileExtension($this->data[$this->alias]['file']['name']);
		}
		if (!empty($this->data[$this->alias]['file']['name'])) {
			$this->data[$this->alias]['filename'] = $this->data[$this->alias]['file']['name'];
		}
		$this->data[$this->alias]['adapter'] = $this->activeAdapter;
		return true;
	}

/**
 * Get a copy of the actual record before we delete it to have it present in afterDelete
 *
 * @return boolean
 */
	public function beforeDelete() {
		$this->record = $this->find('first', array(
			'contain' => array(),
			'conditions' => array(
				$this->alias . '.' . $this->primaryKey => $this->id)));
		if (empty($this->record)) {
			return false;
		}
		return true;
	}

/**
 * @todo error handling, catch exceptions from the adapters
 */
	public function afterDelete() {
		$Storage = $this->storageAdapter($this->record[$this->alias]['adapter']);
		$Storage->delete($this->record[$this->alias]['path']);
	}

/**
 * StorageAdapter
 *
 * @param mixed $adapterName string of adapter configuration or array of settings
 * @param boolean $renewObject
 * @return Gaufrette object as configured by first arg
 */
	public function storageAdapter($adapterName = null, $renewObject = false) {
		if (empty($adapterName)) {
			$adapterName = $this->activeAdapter;
		}

		$isConfigured = true;
		if (is_string($adapterName)) {
			if (!empty($this->adapters[$adapterName])) {
				$adapter = $this->adapters[$adapterName];
			} else {
				throw new RuntimeException(__('Invalid Adapter %s', $adapterName));
			}

			if (!empty($this->adapters[$adapterName]['object']) && $renewObject === false) {
				return $this->adapters[$adapterName]['object'];
			}
		}

		if (is_array($adapterName)) {
			$adapter = $adapterName;
			$isConfigured = false;
		}

		$class = $adapter['adapterClass'];
		$rc = new ReflectionClass($class);
		$adapterObject = $rc->newInstanceArgs($adapter['adapterOptions']);
		$engineObject = new $adapter['class']($adapterObject);
		if ($isConfigured) {
			$this->adapters[$adapterName]['object'] = &$engineObject;
		}
		return $engineObject;
	}

/**
 * Creates a tmp file name and checks the tmp path, creates one if required
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
	public function tmpFile($folder = null, $checkAndCreatePath = true) {
		if (empty($folder)) {
			$path = TMP;
		} else {
			$path = TMP . $folder . DS;
		}

		if ($checkAndCreatePath === true && !is_dir($path)) {
			new Folder($path, true);
		}

		return $path . String::uuid();;
	}

/**
 * Removes the - from the uuid
 *
 * @param string uuid with -
 * @return string uuid without -
 */
	public function stripUuid($uuid) {
		return str_replace('-', '', $uuid);
	}

}