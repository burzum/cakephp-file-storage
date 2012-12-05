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
 * @param array $options
 * @return boolean true on success
 */
	public function beforeSave($options = array()) {
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
 * @param boolean $cascade
 * @return boolean
 */
	public function beforeDelete($cascade = true) {
		if (!parent::beforeDelete($cascade)) {
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
 * Note that we do not call the parent::afterDelete(), we just want to trigger the ImageStorage.afterDelete event but not the FileStorage.afterDelete at the same time!
 *
 * @return void
 */
	public function afterDelete() {
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
 * @throws InternalErrorException
 */
	public function createVersions($data = array(), $format = 'jpg') {
		throw new InternalErrorException(__('file_storage', 'ImageStorage::createVersions is deprecated use the event system.'));
	}

/**
 * Recursive ksort() implementation
 *
 * @param array $array
 * @param integer
 * @return void
 * @link https://gist.github.com/601849
 */
	public function ksortRecursive(&$array, $sortFlags = SORT_REGULAR) {
		if (!is_array($array)) return false;
		ksort($array, $sortFlags);
		foreach ($array as &$arr) {
			$this->ksortRecursive($arr, $sortFlags);
		}
		return true;
	}

/**
 * Image size validation method
 *
 * @param mixed $check
 * @param array $options
 * @return boolean true
 * @throws \InvalidArgumentException
 */
	public function validateImageSize($check, $options) {
		if (!isset($options['height']) && !isset($options['width'])) {
			throw new \InvalidArgumentException(__d('file_storage', 'Invalid image size validation parameters'));
		}

		if (is_string($check)) {
			$imageFile = $check;
		} else {
			$check = array_values($check);
			$check = $check[0];
			if (is_array($check) && isset($check['tmp_name'])) {
				$imageFile = $check['tmp_name'];
			} else {
				$imageFile = $check;
			}
		}

		$imageSizes = $this->getImageSize($imageFile);

		if (isset($options['height'])) {
			$height = Validation::comparison($imageSizes[1], $options['height'][0], $options['height'][1]);
		} else {
			$height = true;
		}

		if (isset($options['width'])) {
			$width = Validation::comparison($imageSizes[0], $options['width'][0], $options['width'][1]);
		} else {
			$width = true;
		}

		if ($height === false || $width === false) {
			return false;
		}

		return true;
	}
}
