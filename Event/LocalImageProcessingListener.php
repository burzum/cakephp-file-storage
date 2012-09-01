<?php
App::uses('CakeEventListener', 'Event');
/**
 * Local Image Processor Event Listener for the CakePHP FileStorage plugin
 *
 * @author Florian Krämer
 * @copy 2012 Florian Krämer
 * @license MIT
 */
class LocalImageProcessingListener implements CakeEventListener {
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
			'ImageStorage.afterDelete' => 'afterDelete'
		);
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

			$tmpFile = $this->_tmpFile($Storage, $record['path']);

			try {
				foreach ($Event->data['operations'] as $version => $operations) {
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

	public function afterSave($Event) {
		if ($this->_checkEvent($Event)) {
			
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