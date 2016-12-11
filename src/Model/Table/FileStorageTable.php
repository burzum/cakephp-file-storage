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
 * The reason for keeping all file references in this table is simply separation
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
		$this->addBehavior('Burzum/FileStorage.FileStorage');
		$this->displayField('filename');
		$this->table('file_storage');
	}

//	/**
//	 * Renews the FileUpload behavior with a new configuration
//	 *
//	 * @param array $options
//	 * @return void
//	 */
//	public function configureUploadValidation($options) {
//		$this->removeBehavior('Burzum/FileStorage.UploadValidator');
//		$this->addBehavior('Burzum/FileStorage.UploadValidator', $options);
//	}

	/**
	 * REMOVED, just keeping the code for reference until refactor is done
	 */
//	public function afterSave(Event $event, EntityInterface $entity, $options) {
//		$this->dispatchEvent('FileStorage.afterSave', [
//			'entity' => $entity,
//			'created' => $event->data['entity']->isNew(),
//			'storage' => $this->getStorageAdapter($entity['adapter'])
//		]);
//		$this->deleteOldFileOnSave($entity);
//		return true;
//	}

	/**
	 * afterSave callback
	 *
	 * @param \Cake\Event\Event $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return boolean
	 */
//	public function afterSave(Event $event, EntityInterface $entity, $options) {
//		$this->dispatchEvent('FileStorage.afterSave', [
//			'record' => $entity,
//			'created' => $event->data['entity']->isNew(),
//			'storage' => $this->getStorageAdapter($entity['adapter'])
//		]);
//		$this->deleteOldFileOnSave($entity);
//		return true;
//	}

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
//	public function deleteOldFileOnSave(EntityInterface $entity, $oldIdField = 'old_file_id') {
//		if (!empty($entity[$oldIdField]) && $entity['model']) {
//			$oldEntity = $this->find()
//				->contain([])
//				->where([
//					$this->alias() . '.' . $this->primaryKey() => $entity[$oldIdField], 'model' => $entity['model']
//				])
//				->first();
//
//			if (!empty($oldEntity)) {
//				return $this->delete($oldEntity);
//			}
//		}
//		return false;
//	}

}
