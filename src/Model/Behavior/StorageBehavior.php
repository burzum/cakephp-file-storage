<?php
namespace Burzum\FileStorage\Model\Behavior;

use ArrayAccess;
use Burzum\FileStorage\Storage\StorageTrait;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\ORM\Behavior;
use Cake\Filesystem\File;

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
 * id, filename, model, foreign_key, path, adapter, filename, mime_type, hash
 *
 * Behavior options:
 *
 * - `defaultStorageConfig`: The default storage config name to use. `Local` by default.
 * - `ignoreEmptyFile`: If not file is present nothing will be saved.
 * - `fileField`: The field that will be checked for a file upload.
 */
class StorageBehavior extends Behavior {

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
	];

	protected function _isFileUploadPresent($entity) {
		$field = $this->config('fileField');
		if ($this->config('ignoreEmptyFile') === true) {
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
	 * @param ArrayAccess $data
	 * @return void
	 */
	public function beforeMarshal(Event $event, ArrayAccess $data) {
		if (!$this->_isFileUploadPresent($data)) {
			return;
		}
		$this->getFileInfoFromUpload($data);
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
			'storage' => $this->storageAdapter($entity['adapter']), // REMOVE ME
			'storageAdapter' => $this->storageAdapter($entity['adapter'])
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
			//'created' => $event->data['entity']->isNew(), // REMOVE ME
			'storage' => $this->storageAdapter($entity['adapter']), // REMOVE ME
			'storageAdapter' => $this->storageAdapter($entity['adapter'])
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
			if (empty($entity->model)) {
				$entity->model = $this->_table->table();
				$entity->identifier = $this->_table->table();
			}
			if (empty($entity->adapter)) {
				$entity->adapter = $this->config('defaultStorageConfig');
			}
		}
	}

	/**
	 * afterDelete callback
	 *
	 * @param \Cake\Event\Event $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return boolean
	 */
	public function afterDelete(Event $event, EntityInterface $entity, $options) {
		$this->dispatchEvent('FileStorage.afterDelete', [
			'entity' => $entity,
			'storageAdapter' => $this->storageAdapter($entity['adapter']),
			'storage' => $this->storageAdapter($entity['adapter'])
		], $this->_table);
	}

	/**
	 * Deletes an old file to replace it with the new one if an old id was passed.
	 * Thought to be called in Model::afterSave() but can be used from any other
	 * place as well like Model::beforeSave() as long as the field data is present.
	 * The old id has to be the UUID of the file_storage record that should be deleted.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param string $oldIdField Name of the field in the data that holds the old id.
	 * @return boolean Returns true if the old record was deleted
	 */
	public function deleteOldFileOnSave(EntityInterface $entity, $oldIdField = 'old_file_id') {
		if (!empty($entity[$oldIdField]) && $entity['model']) {
			$oldEntity = $this->_table->find()
				->contain([])
				->where([
					$this->_table->alias() . '.' . $this->_table->primaryKey() => $entity[$oldIdField],
					'model' => $entity['model']
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
	 * - sets the adapter by default to local if not already set
	 * - sets the model field to the table name if not already set
	 *
	 * @param array|\ArrayAccess $upload
	 * @param string $field
	 * @return void
	 */
	public function getFileInfoFromUpload(&$upload, $field = 'file') {
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

}
