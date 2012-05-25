<?php
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');
App::uses('FileStorageAppModel', 'FileStorage.Model');
App::uses('StorageManager', 'FileStorage.Lib');
App::uses('FileStorageUtils', 'FileStorage.Utility');
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
 * Renews the FileUpload behavior with a new configuration
 *
 * @param array $options
 * @return void
 */
	public function configureUploadValidation($options) {
		$this->Behaviors->unload('FileStorage.UploadValidator');
		$this->Behaviors->load('FileStorage.UploadValidator', $options);
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
		if (empty($this->data[$this->alias]['adapter'])) {
			$this->data[$this->alias]['adapter'] = 'Local';
		}
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
		$Storage = Storagemanager::adapter($this->record[$this->alias]['adapter']);
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
		return Storagemanager::adapter($adapterName, $renewObject);
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

/**
 * 
 */
	public function fsPath($type, $string, $idFolder = true) {
		$string = str_replace('-', '', $string);
		$path = $type . DS . FileStorageUtils::randomPath($string);
		if ($idFolder) {
			$path .= $string . DS;
		}
		return $path;
	}

/**
 * Return file extension from a given filename
 *
 * @param string
 * @return boolean string or false
 */
	public function fileExtension($path) {
		if (file_exists($path)) {
			return pathinfo($path, PATHINFO_EXTENSION);
		} else {
			return substr(strrchr($path,'.'), 1);
		}
	}

}