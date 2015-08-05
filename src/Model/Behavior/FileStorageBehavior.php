<?php
namespace Burzum\FileStorage\Model\Behavior;

use Burzum\FileStorage\Model\Behavior\Event\EventDispatcherTrait;
use Burzum\FileStorage\Storage\StorageTrait;
use Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventDispatcherInterface;
use Cake\Filesystem\File;
use Cake\Log\LogTrait;
use Cake\ORM\Behavior;

/**
 * FileStorageTable
 *
 * @author Florian Krämer
 * @author Robert Pustułka
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class FileStorageBehavior extends Behavior implements EventDispatcherInterface {

	use EventDispatcherTrait;
	use LogTrait;
	use PathBuilderTrait;
	use StorageTrait;

/**
 *
 * @var array
 */
	protected $_defaultConfig = [
		'implementedMethods' => [
			'getFileInfoFromUpload' => 'getFileInfoFromUpload',
			'deleteOldFileOnSave' => 'deleteOldFileOnSave',
			'fullFilePath' => 'fullFilePath',
			'fileUrl' => 'fileUrl'
		]
	];

/**
 *
 * @param array $config
 * @return void
 */
	public function initialize(array $config) {
		$this->_table->record = [];
		$this->_eventManager = $this->_table->eventManager();

		parent::initialize($config);
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
			$upload['filesize'] = $File->size();
			$upload['mime_type'] = $File->mime();
		}
		if (!empty($upload[$field]['name'])) {
			$upload['extension'] = pathinfo($upload[$field]['name'], PATHINFO_EXTENSION);
			$upload['filename'] = $upload[$field]['name'];
		}
		if (empty($upload['model'])) {
			$upload['model'] = $this->_table->table();
		}
		if (empty($upload['adapter'])) {
			$upload['adapter'] = 'Local';
		}
	}

/**
 * beforeSave callback
 *
 * @param \Cake\Event\Event $event
 * @param \Cake\Datasource\EntityInterface $entity
 * @param array $options
 * @return boolean true on success
 */
	public function beforeSave(Event $event, EntityInterface $entity, $options) {
		$this->getFileInfoFromUpload($entity);
		$storageEvent = $this->dispatchEvent('FileStorage.beforeSave', [
			'record' => $entity,
			'storage' => $this->storageAdapter($entity->get('adapter'))
		]);
		if ($storageEvent->isStopped()) {
			return false;
		}
		return true;
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
			'storage' => $this->storageAdapter($entity->get('adapter')),
			'created' => $entity->isNew()
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
		$primaryKey = $this->_table->primaryKey();
		$this->_table->record = $this->_table->find()
			->contain([])
			->where([
				$this->_table->aliasField($primaryKey) => $entity->get($primaryKey)
			])
			->first();

		if (empty($this->_table->record)) {
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
		$this->dispatchEvent('FileStorage.afterDelete', [
			'record' => $entity,
			'storage' => $this->storageAdapter($entity->get('adapter'))
		]);
		return true;
	}

/**
 * Deletes an old file to replace it with the new one if an old id was passed.
 *
 * Thought to be called in Model::afterSave() but can be used from any other
 * place as well like Model::beforeSave() as long as the field data is present.
 *
 * The old id has to be the UUID of the file_storage record that should be deleted.
 *
 * @param \Cake\Datasource\EntityInterface $entity
 * @param string $oldIdField Name of the field in the data that holds the old id.
 * @return boolean Returns true if the old record was deleted
 */
	public function deleteOldFileOnSave(EntityInterface $entity, $oldIdField = 'old_file_id') {
		if ($entity->has($oldIdField) && $entity->has('model')) {
			$oldEntity = $this->_table->find()
				->contain([])
				->where([
					$this->_table->aliasField($this->_table->primaryKey()) => $entity->get($oldIdField),
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
 * Returns full file path for an entity.
 *
 * @param \Cake\Datasource\EntityInterface $entity
 * @param array $options
 * @return string
 */
	public function fullFilePath(EntityInterface $entity, array $options = []) {
		$pathBuilder = $this->createPathBuilder($entity['adapter']);
		return $pathBuilder->fullPath($entity, $options);
	}

/**
 * Returns file url for an entity.
 *
 * @param \Cake\Datasource\EntityInterface $entity
 * @param array $options
 * @return string
 */
	public function fileUrl(EntityInterface $entity, array $options = []) {
		$pathBuilder = $this->createPathBuilder($entity['adapter']);
		return $pathBuilder->url($entity, $options);
	}
}
