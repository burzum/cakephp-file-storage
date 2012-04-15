<?php
App::uses('FileStorage', 'FileStorage.Model');
/**
 * Image
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class Image extends FileStorage {
/**
 * Name
 *
 * @var string
 */
	public $name = 'Image';

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
		'FileUpload' => array(
			'localFile' => true,
			'validateUpload' => false,
			'allowedExtensions' => array('jpg', 'png', 'gif')
		),
	);

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
		parent::beforeSave($options);
		
		return true;
	}

/**
 * afterSave callback
 *
 * @param boolean
 * @return void
 */
	public function afterSave($created) {
		if ($created) {
			if ($this->createVersions === true) {
				$record = $this->data[$this->alias];
				$this->createVersions($record['id'], $record['file']['tmp_name'], $record['model'], $record['extension']);
				$this->save($this->data, array('callbacks' => false, 'validate' => false));
			}
		}
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

		if (!empty($this->record[$this->alias]['adapter'])) {
			$adapter = $this->record[$this->alias]['adapter'];
			if (!method_exists($this, 'afterDelete' . $adapter . 'Adapter')) {
				return false;
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
		$adapter = $this->record[$this->alias]['adapter'];
		$method = 'afterDelete' . $adapter . 'Adapter';
		if (!$this->{$method}()) {
			$this->log('Could not delete stored file:', 'file_storage');
			$this->log($this->record, 'file_storage');
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
			App::uses('Folder', 'Utility');
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
	public function createVersions($uuid, $imageFile, $model, $format = 'jpg') {
		$filename = $this->stripUuid($uuid);
		$sizes = Configure::read('Media.imageSizes.' . $model);
		$path = $this->fsPath('images' . DS . $model, $uuid);
		$this->data[$this->alias]['path'] = $path;

		$Gaufrette = $this->storageAdapter('Local');
		$Gaufrette->write($path . $filename . '.' . $format, file_get_contents($imageFile), true);

		foreach ($sizes as $type => $operations) {
			$hash = $this->hashOperations($operations);
			$image = $this->processImage($imageFile, null, array('format' => $format), $operations);
			$Gaufrette->write($path . $filename . '.' . $hash . '.' . $format, $image->get($format), true);
		}
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
