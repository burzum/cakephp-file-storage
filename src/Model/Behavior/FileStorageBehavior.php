<?php
/**
 * File Storage Plugin for CakePHP
 *
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Model\Behavior;

use ArrayAccess;
use Burzum\FileStorage\Storage\StorageTrait;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\Filesystem\File;
use Cake\ORM\Behavior;

/**
 * Storage Behavior
 *
 * The behavior will fire events to deal with the file storage logic and gather
 * the data from the uploaded file that is stored. If you're looking for the
 * actual storage logic and processing take a look at the Storage Listeners.
 *
 * The behavior encapsulates all the logic that is needed to make a table work
 * as reference table for keeping the references to all the stored files.
 *
 * A table that will work with this behavior requires at least these fields:
 * id, filename, identifier, foreign_key, path, adapter, mime_type, hash
 *
 * Behavior options:
 *
 * - `defaultStorageConfig`: The default storage config name to use. `Local` by default.
 * - `ignoreEmptyFile`: If not file is present nothing will be saved.
 * - `fileField`: The field that will be checked for a file upload.
 */
class FileStorageBehavior extends Behavior {

	use EventDispatcherTrait;
	use StorageTrait;

	/**
	 * Default config
	 *
	 * @var array
	 */
	protected $_defaultConfig = [
		'defaultStorageConfig' => 'Local',
		'ignoreEmptyFile' => true,
		'fileField' => 'file',
		'getFileHash' => false,
	];

	/**
	 * Checks if a file upload is present.
	 *
	 * @param \Cake\Datasource\EntityInterface|array $entity
	 * @return bool
	 */
	protected function _isFileUploadPresent($entity) {
		$field = $this->getConfig('fileField');
		if ($this->getConfig('ignoreEmptyFile') === true) {
			if (!isset($entity[$field]['error']) || $entity[$field]['error'] === UPLOAD_ERR_NO_FILE) {
				return false;
			}
		}

		return true;
	}

	/**
	 * beforeMarshal callback
	 *
	 * @param \Cake\Event\Event $event
	 * @param \ArrayAccess $data
	 * @return void
	 */
	public function beforeMarshal(Event $event, ArrayAccess $data) {
		if (!$this->_isFileUploadPresent($data)) {
			return;
		}

		$this->_getFileInfoFromUpload($data);
	}

	/**
	 * beforeSave callback
	 *
	 * @param \Cake\Event\Event $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @return void
	 */
	public function beforeSave(Event $event, EntityInterface $entity) {
		if (!$this->_isFileUploadPresent($entity)) {
			$event->stopPropagation();

			return;
		}

		$this->_checkEntityBeforeSave($entity);

		$this->dispatchEvent('FileStorage.beforeSave', [
			'entity' => $entity,
			'storageAdapter' => $this->getStorageAdapter($entity->get('adapter'))
		], $this->_table);
	}

	/**
	 * afterSave callback
	 *
	 * @param \Cake\Event\Event $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return void
	 */
	public function afterSave(Event $event, EntityInterface $entity, $options) {
		$this->dispatchEvent('FileStorage.afterSave', [
			'entity' => $entity,
			'storageAdapter' => $this->getStorageAdapter($entity->get('adapter'))
		], $this->_table);
	}

	/**
	 * _checkEntityBeforeSave
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @return void
	 */
	protected function _checkEntityBeforeSave(EntityInterface &$entity) {
		if ($entity->isNew()) {
			if (!$entity->has('model')) {
				$entity->set('model', $this->_table->getTable());
			}

			if (!$entity->has('adapter')) {
				$entity->set('adapter', $this->getConfig('defaultStorageConfig'));
			}

			$fileHashMethod = $this->getConfig('getFileHash');
			if ($fileHashMethod) {
				if ($fileHashMethod === true) {
					$fileHashMethod = 'sha1';
				}
				$entity->set('hash', StorageUtils::getFileHash($entity->get('file')['tmp_name'], $fileHashMethod));
			}
		}
	}

	/**
	 * afterDelete callback
	 *
	 * @param \Cake\Event\Event $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return bool
	 */
	public function afterDelete(Event $event, EntityInterface $entity, $options) {
		$this->dispatchEvent('FileStorage.afterDelete', [
			'entity' => $entity,
			'storageAdapter' => $this->getStorageAdapter($entity->get('adapter')),
		], $this->_table);
	}

	/**
	 * Deletes an old file to replace it with the new one if an old id was passed.
	 *
	 * Thought to be called in Table::afterSave() but can be used from any other
	 * place as well like Table::beforeSave() as long as the field data is present.
	 * The old id has to be the UUID of the file_storage record that should be deleted.
	 *
	 * Table::deleteAll() is intentionally not used because it doesn't trigger
	 * callbacks.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param string $oldIdField Name of the field in the data that holds the old id.
	 * @return bool Returns true if the old record was deleted
	 */
	public function deleteOldFileOnSave(EntityInterface $entity, $oldIdField = 'old_file_id') {
		if (!empty($entity->get($oldIdField)) && $entity->get('model')) {
			$oldEntity = $this->_table->find()
				->contain([])
				->where([
					$this->_table->getAlias() . '.' . $this->_table->getPrimaryKey() => $entity->get($oldIdField),
					'model' => $entity->get('model')
				])
				->first();

			if (!empty($oldEntity)) {
				return $this->_table->delete($oldEntity);
			}
		}

		return false;
	}

	/**
	 * Gets information about the file that is being uploaded.
	 *
	 * - gets the file size
	 * - gets the mime type
	 * - gets the extension if present
	 *
	 * @param array|\ArrayAccess $upload
	 * @param string $field
	 * @return void
	 */
	public function _getFileInfoFromUpload(&$upload, $field = 'file') {
		if (!empty($upload[$field]['tmp_name'])) {
			$File = new File($upload[$field]['tmp_name']);
			$upload['filesize'] = filesize($upload[$field]['tmp_name']);
			$upload['mime_type'] = $File->mime();
		}

		if (!empty($upload[$field]['name'])) {
			$upload['extension'] = pathinfo($upload[$field]['name'], PATHINFO_EXTENSION);
			$upload['filename'] = $upload[$field]['name'];
		}
	}

	/**
	 * Don't use Table::deleteAll() if you don't want to end up with orphaned
	 * files! The reason for that is that deleteAll() doesn't fire the
	 * callbacks. So the events that will remove the files won't get fired.
	 *
	 * @param array $conditions Query::where() array structure.
	 * @return int Number of deleted records / files
	 */
	public function deleteAllFiles($conditions) {
		$results = $this->find()
			->select((array)$this->primaryKey())
			->where($conditions)
			->all();

		if ($results->count() > 0) {
			foreach ($results as $result) {
				$this->delete($result);
			}
		}

		return $results->count();
	}

}
