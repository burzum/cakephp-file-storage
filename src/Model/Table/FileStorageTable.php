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
}
