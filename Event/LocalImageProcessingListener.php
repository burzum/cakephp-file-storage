<?php
App::uses('CakeEventListener', 'Event');
/**
 * Local Image Processor Event Listener for the CakePHP FileStorage plugin
 *
 * @author Florian Krämer
 * @copy 2012 Florian Krämer
 * @license MIT
 */
class LocalImageProcessingListener extends Object implements CakeEventListener {
/**
 * Implemented Events
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'ImageStorage.createVersion' => 'createVersions',
			'ImageStorage.removeVersion' => 'removeVersions',
			'ImageStorage.afterSave' => 'afterSave',
			'ImageStorage.afterDelete' => 'afterDelete',
			'FileStorage.ImageHelper.imagePath' => 'imagePath',
		);
	}

/**
 * Creates the different versions of images that are configured
 * 
 * @param Model $Model
 * @param array $record
 * @param array $operations
 * @return void
 */
	protected function _createVersions($Model, $record, $operations1) {
		try {
			$Storage = StorageManager::adapter($record['adapter']);
			$tmpFile = $this->_tmpFile($Storage, $record['path']);

			foreach ($operations1 as $version => $operations) {
				$hash = $Model->hashOperations($operations);
				$string = substr($record['path'], 0, - (strlen($record['extension'])) -1);
				$string .= '.' . $hash . '.' . $record['extension'];

				if ($Storage->has($string)) {
					continue;
				}

				$image = $Model->processImage($tmpFile, null, array('format' => $record['extension']), $operations);
				$result = $Storage->write($string, $image->get($record['extension']), true);
			}
		} catch (Exception $e) {
			$this->log($e->getMessage(), 'file_storage');
			unlink($tmpFile);
			throw $e;
		}

		unlink($tmpFile);
	}

/**
 * Creates versions for a given image record
 *
 * @param CakeEvent $Event
 * @return void
 */
	public function createVersions($Event) {
		if ($this->_checkEvent($Event)) {
			$Model = $Event->subject();
			$Storage = $Event->data['storage'];
			$record = $Event->data['record'][$Model->alias];

			$this->_createVersions($Model, $record[$Model->alias], $Event->data['operations']);

			$Event->stopPropagation();
		}
	}

/**
 * Removes versions for a given image record
 *
 * @param CakeEvent $Event
 * @return void
 */
	public function removeVersions($Event) {
		if ($this->_checkEvent($Event)) {
			$Model = $Event->subject();
			$Storage = $Event->data['storage'];
			$record = $Event->data['record'][$Model->alias];

			foreach ($Event->data['operations'] as $version => $operations) {
				$hash = $Model->hashOperations($operations);
				$string = substr($record['path'], 0, - (strlen($record['extension'])) -1);
				$string .= '.' . $hash . '.' . $record['extension'];

				try {
					if ($Storage->has($string)) {
						$Storage->delete($string);
					}
				} catch (Exception $e) {
					$this->log($e->getMessage(), 'file_storage');
				}
			}

			$Event->stopPropagation();
		}
	}

/**
 * afterDelete
 *
 * @param CakeEvent $Event
 * @return void
 */
	public function afterDelete($Event) {
		if ($this->_checkEvent($Event)) {
			$path = Configure::read('Media.basePath') . $this->record[$this->alias]['path'];
			if (is_dir($path)) {
				$Folder = new Folder($path);
				return $Folder->delete();
			}
			return false;
		}
	}

/**
 * afterSave
 *
 * @param CakeEvent $Event
 * @return void
 */
	public function afterSave($Event) {
		if ($this->_checkEvent($Event)) {
			$Model = $Event->subject();
			$Storage = StorageManager::adapter($Model->data[$Model->alias]['adapter']);
			$record = $Model->data;

			try {
				$id = $record[$Model->alias][$Model->primaryKey];
				$filename = $Model->stripUuid($id);
				$file = $record[$Model->alias]['file'];
				$format = $record[$Model->alias]['extension'];
				$sizes = Configure::read('Media.imageSizes.' . $record[$Model->alias]['model']);
				$path = $Model->fsPath('images' . DS . $record[$Model->alias]['model'], $id);
				$record[$Model->alias]['path'] = $path . $filename . '.' . $format;

				$result = $Storage->write($record[$Model->alias]['path'], file_get_contents($file['tmp_name']), true);

				$Model->save($record, array(
					'validate' => false,
					'callbacks' => false));

				$this->_createVersions($Model, $record[$Model->alias], Configure::read('Media.imageSizes.' . $record[$Model->alias]['model']));

			} catch (Exception $e) {
				$this->log($e->getMessage(), 'file_storage');
			}
		}
	}

/**
 * 
 */
	public function imagePath($Event) {
		if ($Event->data['image']['adapter'] == 'Local') {
			$Helper = $Event->subject();
			extract($Event->data);

			$path = $Helper->normalizePath($image['path']);
			$path = $path . str_replace('-', '', $image['id']);
			$path .= '.' . $hash . '.' . $image['extension'];

			$Event->data['path'] = $path;
			$Event->stopPropagation();
		}
	}

/**
 * It is required to get the file first and write it to a tmp file
 *
 * The adapter might not be one that is using a local file system, so we first
 * get the file from the storage system, store it locally in a tmp file and
 * later load the new file that was generated based on the tmp file into the
 * storage adapter. This method here just generates the tmp file.
 *
 * @param
 * @param
 * @return
 */
	protected function _tmpFile($Storage, $path) {
		try {
			if (!is_dir(TMP . 'image-processing')) {
				mkdir(TMP . 'image-processing');
			}

			$tmpFile = TMP . 'image-processing' . DS . String::uuid();
			$imageData = $Storage->read($path);

			file_put_contents($tmpFile, $imageData);
			return $tmpFile;
		} catch (Exception $e) {
			return false;
		}
	}

/**
 * Check if the event can be processed
 *
 * @param CakeEvent $Event
 * @return boolean
 */
	protected function _checkEvent($Event) {
		$Model = $Event->subject();
		return ($Model instanceOf ImageStorage && isset($Event->data['record'][$Model->alias]['adapter']) && $Event->data['record'][$Model->alias]['adapter'] == 'Local');
	}
}