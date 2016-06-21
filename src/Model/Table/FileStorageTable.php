<?php
namespace Burzum\FileStorage\Model\Table;

use Cake\ORM\Table;

/**
 * FileStorageTable
 *
 * Records in this table act as a reference to the real location of the stored
 * file data. All information of a row can be used to build a path to the file.
 * So the data in this table is pretty important.
 *
 * The reason for keeping all file references in this table is simply speration
 * of concerns: We separate the files from the other modules of the application
 * and threat them centralized and all the same.
 *
 * The actual storing and removing of the file data is handled by the Storage
 * Behavior that is attached to this table.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
class FileStorageTable extends Table {

	/**
	 * Initialize
	 *
	 * @param array $config
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->primaryKey('id');
		$this->addBehavior('Timestamp');
		$this->addBehavior('Burzum/FileStorage.Storage');
		$this->displayField('filename');
		$this->table('file_storage');
	}

	/**
	 * Renews the FileUpload behavior with a new configuration
	 *
	 * @param array $options
	 * @return void
	 */
	public function configureUploadValidation($options) {
		$this->removeBehavior('Burzum/FileStorage.UploadValidator');
		$this->addBehavior('Burzum/FileStorage.UploadValidator', $options);
	}

	/**
	 * REMOVED, just keeping the code for reference until refactor is done
	 */
//	public function afterSave(Event $event, EntityInterface $entity, $options) {
//		$this->dispatchEvent('FileStorage.afterSave', [
//			'entity' => $entity,
//			'created' => $event->data['entity']->isNew(),
//			'storage' => $this->storageAdapter($entity['adapter'])
//		]);
//		$this->deleteOldFileOnSave($entity);
//		return true;
//	}

	/**
	 * REMOVED, just keeping the code for reference until refactor is done
	 */

//	public function afterDelete(Event $event, EntityInterface $entity, $options) {
//		$event = $this->dispatchEvent('FileStorage.afterDelete', [
//			'entity' => $entity,
//			'storage' => $this->storageAdapter($entity['adapter'])
//		]);
//		if ($event->isStopped()) {
//			return $event->result;
//		}
//		try {
//			$Storage = $this->storageAdapter($entity['adapter']);
//			$Storage->delete($entity['path']);
//			return true;
//		} catch (\Exception $e) {
//			$this->log($e->getMessage());
//		}
//		return false;
//	}

	public function getFileInfoFromUpload(&$upload, $field = 'file') {
		if (!empty($upload[$field]['tmp_name'])) {
			$File = new File($upload[$field]['tmp_name']);
			$upload['filesize'] = $File->size();
			$upload['mime_type'] = $File->mime();
		}
		if (!empty($upload[$field]['name'])) {
			$upload['extension'] = pathinfo($upload[$field]['name'], PATHINFO_EXTENSION);
			$upload['filename'] = $upload[$field]['name'];
		}
	}

	/**
	 * afterSave callback
	 *
	 * @param \Cake\Event\Event $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return boolean
	 */
	public function afterSave(Event $event, EntityInterface $entity, $options) {
		$this->dispatchEvent('FileStorage.afterSave', [
			'record' => $entity,
			'created' => $event->data['entity']->isNew(),
			'storage' => $this->storageAdapter($entity['adapter'])
		]);
		$this->deleteOldFileOnSave($entity);
		return true;
	}

	/**
	 * Get a copy of the actual record before we delete it to have it present in afterDelete
	 *
	 * @param \Cake\Event\Event $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @return boolean
	 */
	public function beforeDelete(Event $event, EntityInterface $entity) {
		$this->record = $this->find()
			->contain([])
			->where([
				$this->alias() . '.' . $this->primaryKey() => $entity->{$this->primaryKey()}
			])
			->first();

		if (empty($this->record)) {
			return false;
		}

		return true;
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
		$event = $this->dispatchEvent('FileStorage.afterDelete', [
			'record' => $entity,
			'storage' => $this->storageAdapter($entity['adapter'])
		]);
		if ($event->isStopped()) {
			return $event->result;
		}
		try {
			$Storage = $this->storageAdapter($entity['adapter']);
			$Storage->delete($entity['path']);
			return true;
		} catch (\Exception $e) {
			$this->log($e->getMessage());
		}
		return false;
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
			$oldEntity = $this->find()
				->contain([])
				->where([
					$this->alias() . '.' . $this->primaryKey() => $entity[$oldIdField], 'model' => $entity['model']
				])
				->first();

			if (!empty($oldEntity)) {
				return $this->delete($oldEntity);
			}
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function dispatchEvent($name, $data = null, $subject = null) {
		$data['table'] = $this;
		return parent::dispatchEvent($name, $data, $subject);
	}

	/**
	 * @deprecated Use storageAdapter() instead.
	 */
	public function getStorageAdapter($configName, $renewObject = false) {
		return $this->storageAdapter($configName, $renewObject);
	}
}
