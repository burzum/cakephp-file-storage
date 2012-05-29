<?php
App::uses('FileStorage', 'FileStorage.Model');
App::uses('Folder', 'Utility');
/**
 * Image
 *
 * @author Florian Kr�mer
 * @copyright 2012 Florian Kr�mer
 * @license MIT
 */
class ImageStorage extends FileStorage {
/**
 * Table to use
 *
 * @var mixed
 */
	public $useTable = 'file_storage';

/**
 * Create revisions after upload or not
 *
 * @var boolean default true
 */
	public $createVersions = true;

/**
 * Behaviours
 *
 * @var array
 */
	public $actsAs = array(
		'Imagine.Imagine',
		'FileStorage.UploadValidator' => array(
			'localFile' => true,
			'validateUpload' => false,
			'allowedExtensions' => array('jpg', 'png', 'gif')
		),
	);

/**
 * beforeSave callback
 *
 * @return boolean true on success
 */
	public function beforeSave($options) {
		parent::beforeSave($options);

		$this->log('beforeSave called', 'imageupload');
		return true;
	}

/**
 * afterSave callback
 *
 * @param boolean
 * @return void
 */
	public function afterSave($created) {
		parent::afterSave($created);

		if ($created) {
			if ($this->createVersions === true) {
				$this->data[$this->alias][$this->primaryKey] = $this->getLastInsertId();
				if ($this->createVersions($this->data)) {
					return $this->save($this->data, array('callbacks' => false, 'validate' => false));
				}
				$this->delete($this->id);
				return false;
			}
		}
	}

/**
 * Get a copy of the actual record before we delete it to have it present in afterDelete
 *
 * @return boolean
 */
	public function beforeDelete() {
		if (!parent::beforeDelete()) {
			return false;
		}

		if (!empty($this->record[$this->alias]['adapter'])) {
			$adapter = $this->record[$this->alias]['adapter'];
			$method = 'beforeDelete' . $adapter . 'Adapter';
			if (method_exists($this, $method)) {
				if (!$this->{$method}) {
					return false;
				}
			}
		}
		return true;
	}

/**
 * After the main file was deleted remove the the thumbnails
 *
 * @return void
 */
	public function afterDelete() {
		if (!empty($this->record[$this->alias]['adapter'])) {
			$adapter = $this->record[$this->alias]['adapter'];
			$method = 'afterDelete' . $adapter . 'Adapter';
			if (method_exists($this, $method)) {
				if (!$this->{$method}()) {
					$this->log('Could not delete stored file:', 'file_storage');
					$this->log($this->record, 'file_storage');
				}
			}
		}
	}

/**
 * Removes an image from the local file storage adapter
 *
 * @return boolean True on success
 */
	protected function afterDeleteLocalAdapter() {
		$path = Configure::read('Media.basePath') . $this->record[$this->alias]['path'];
		if (is_dir($path)) {
			$Folder = new Folder($path);
			return $Folder->delete();
		}
		return false;
	}

/**
 * @param array $results
 * @return array
 */
	public function afterFind($results) {
		return $results;
	}

/**
 * Serializes and then hashes an array of operations that are applied to an image
 *
 * @param array $operations
 * @return array
 */
	public function hashOperations($operations) {
		$this->ksortRecursive($operations);
		return substr(md5(serialize($operations)), 0, 8);
	}

/**
 * Generate hashes
 *
 * @param string
 * @return void
 */
	public function generateHashes($configPath = 'Media') {
		$imageSizes = Configure::read($configPath . '.imageSizes');
		$this->ksortRecursive($imageSizes);
		foreach ($imageSizes as $model => $version) {
			foreach ($version as $name => $operations) {
				Configure::write($configPath . '.imageHashes.' . $model . '.' . $name, $this->hashOperations($operations));
			}
		}
	}

/**
 * Creates the versions of the image uploads
 *
 * @param string 
 * @param string 
 * @param string 
 * @param string 
 * @return
 */
	public function createVersions($data = array(), $format = 'jpg') {
		if (empty($data)) {
			$data = $this->data;
		}
		extract($data[$this->alias]);

		$filename = $this->stripUuid($id);
		$sizes = Configure::read('Media.imageSizes.' . $model);
		$path = $this->fsPath('images' . DS . $model, $id);
		$this->data[$this->alias]['path'] = $path;

		try {
			$Gaufrette = StorageManager::adapter($adapter);
			$result = $Gaufrette->write($path . $filename . '.' . $format, file_get_contents($file['tmp_name']), true);
			foreach ($sizes as $type => $operations) {
				$hash = $this->hashOperations($operations);
				$image = $this->processImage($file['tmp_name'], null, array('format' => $format), $operations);
				$result = $Gaufrette->write($path . $filename . '.' . $hash . '.' . $format, $image->get($format), true);
			}
		} catch (Exception $e) {
			$this->log($e->getMessage(), 'file_storage');
			//$this->delete($this->id);
			return false;
		}

		return true;
	}

/**
 * Recursive ksort() implementation
 *
 * @param array $array
 * @param integer
 * @return void
 * @link https://gist.github.com/601849
 */
	public function ksortRecursive(&$array, $sort_flags = SORT_REGULAR) {
		if (!is_array($array)) return false;
		ksort($array, $sort_flags);
		foreach ($array as &$arr) {
			$this->ksortRecursive($arr, $sort_flags);
		}
		return true;
	}

}
