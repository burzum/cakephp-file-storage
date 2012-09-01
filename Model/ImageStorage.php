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
		if (!parent::beforeSave($options)) {
			return false;
		}

		$Event = new CakeEvent('ImageStorage.beforeSave', $this, array(
			'record' => $this->data));
		//CakeEventManager::instance()->dispatch($Event);

		if ($Event->isStopped()) {
			return false;
		}

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
			$this->data[$this->alias][$this->primaryKey] = $this->getLastInsertId();

			if ($this->createVersions === true) {

				$Event = new CakeEvent('ImageStorage.afterSave', $this, array(
					'created' => $created,
					'storage' => StorageManager::adapter($this->data[$this->alias]['adapter']),
					'record' => $this->data));
				CakeEventManager::instance()->dispatch($Event);
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

		$Event = new CakeEvent('ImageStorage.beforeDelete', $this, array(
			'record' => $this->record,
			'storage' => StorageManager::adapter($this->record[$this->alias]['adapter'])));
		CakeEventManager::instance()->dispatch($Event);

		if ($Event->isStopped()) {
			return false;
		}

		return true;
	}

/**
 * After the main file was deleted remove the the thumbnails
 *
 * @return void
 */
	public function afterDelete() {
		parent::afterDelete();

		$Event = new CakeEvent('ImageStorage.afterDelete', $this, array(
			'record' => $this->record,
			'storage' => StorageManager::adapter($this->record[$this->alias]['adapter'])));
		CakeEventManager::instance()->dispatch($Event);
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
 * @deperacted This has been replaced by Events
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
