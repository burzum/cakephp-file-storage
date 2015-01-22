<?php
namespace Burzum\FileStorage\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Utility\Folder;
use Cake\Utility\File;
use Cake\Utility\String;
use Burzum\FileStorage\Lib\StorageManager;

/**
 * FileStorageTable
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class FileStorageTable extends Table {

/**
 * Name
 *
 * @var string
 */
	public $name = 'FileStorage';

/**
 * Table name
 *
 * @var string
 */
	public $useTable = 'file_storage';

/**
 * The record that was deleted
 *
 * This gets set in the beforeDelete() callback so that the data is available
 * in the afterDelete() callback
 *
 * @var array
 */
	public $record = array();

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'adapter' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty')
			)
		),
		'path' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty')
			)
		),
		'foreign_key' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty')
			)
		),
		'model' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty')
			)
		)
	);

/**
 * Initialize
 *
 * @param array $config
 * @return void
 */
	public function initialize(array $config) {
		parent::initialize($config);
		//$this->addBehavior('Burzum/FileStorage.UploadValidator');
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
 * beforeSave callback
 *
 * @param array $options
 * @return boolean true on success
 */
	public function beforeSave(Event $event, Entity $entity, $options) {
		if (!empty($event->data['entity']['file']['tmp_name'])) {
			$File = new File($event->data['entity']['file']['tmp_name']);
			$event->data['entity']['filesize'] = $File->size();
			$event->data['entity']['mime_type'] = $File->mime();
		}
		if (!empty($event->data['entity']['file']['name'])) {
			$event->data['entity']['extension'] = pathinfo($event->data['entity']['file']['name'], PATHINFO_EXTENSION);
			$event->data['entity']['filename'] = $event->data['entity']['file']['name'];
		}
		if (empty($event->data['entity']['table'])) {
			$event->data['entity']['table'] = $this->table();
			$event->data['entity']['model'] = $this->table(); // Backward compatibility
		}
		if (empty($event->data['entity']['adapter'])) {
			$event->data['entity']['adapter'] = 'Local'; // Backward compatibility
			$event->data['entity']['adapter_config'] = 'Local';
		}
		$Event = new Event('FileStorage.beforeSave', $this, array(
			'record' => $entity,
			'storage' => $this->getStorageAdapter($event->data['entity']['adapter'])
		));
		$this->getEventManager()->dispatch($Event);
		if ($Event->isStopped()) {
			return false;
		}
		return true;
	}

/**
 * afterSave callback
 *
 * @param boolean $created
 * @param array $options
 * @return void
 */
	public function afterSave(Event $event, Entity $entity, $options) {
		if ($event->data['entity']->isNew) {
			$event->data['entity'][$this->primaryKey] = $this->getLastInsertId();
		}
		$Event = new Event('FileStorage.afterSave', $this, array(
			'created' => $event->data['entity']->isNew,
			'record' => $entity,
			'storage' => $this->getStorageAdapter($event->data['entity']['adapter'])
		));
		$this->getEventManager()->dispatch($Event);
		$this->deleteOldFileOnSave($entity);
		return true;
	}

/**
 * Get a copy of the actual record before we delete it to have it present in afterDelete
 *
 * @param \Cake\Event\Event $event
 * @param \Burzum\FileStorage\Model\Table\Entity $entity
 * @param array $options
 * @return boolean
 */
	public function beforeDelete(\Cake\Event\Event $event, \Cake\ORM\Entity $entity) {
		if (!parent::beforeDelete($event, $entity)) {
			return false;
		}

		$this->record = $this->find('first', array(
			'contain' => array(),
			'conditions' => array(
				$this->alias() . '.' . $this->primaryKey() => $this->id
			)
		));

		if (empty($this->record)) {
			return false;
		}

		return true;
	}

/**
 * afterDelete callback
 *
 * @param \Cake\Event\Event $event
 * @param \Burzum\FileStorage\Model\Table\Entity $entity
 * @param array $options
 * @return boolean
 */
	public function afterDelete(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, $options) {
		try {
			$Storage = $this->getStorageAdapter($entity['adapter']);
			$Storage->delete($entity['path']);
		} catch (Exception $e) {
			$this->log($e->getMessage(), 'file_storage');
			return false;
		}

		$Event = new Event('FileStorage.afterDelete', $this, array(
			'record' => $this->record,
			'storage' => $this->getStorageAdapter($entity['adapter'])));
		$this->getEventManager()->dispatch($Event);

		return true;
	}

/**
 * Get a storage adapter from the StorageManager
 *
 * @param string $adapterName
 * @param boolean $renewObject
 * @return \Gaufrette\Adapter
 */
	public function getStorageAdapter($adapterName, $renewObject = false) {
		return StorageManager::adapter($adapterName, $renewObject);
	}

/**
 * Deletes an old file to replace it with the new one if an old id was passed.
 *
 * Thought to be called in Model::afterSave() but can be used from any other
 * place as well like Model::beforeSave() as long as the field data is present.
 *
 * The old id has to be the UUID of the file_storage record that should be deleted.
 *
 * @param string $oldIdField Name of the field in the data that holds the old id
 * @return boolean Returns true if the old record was deleted
 */
	public function deleteOldFileOnSave(Entity $entity, $oldIdField = 'old_file_id') {
		if (!empty($entity[$oldIdField]) && $entity['model']) {
			return $this->delete($entity[$oldIdField]);
		}
		return false;
	}

/**
 * Returns an EventManager instance
 *
 * @return \Cake\Event\EventManager;
 */
	public function getEventManager() {
		return EventManager::instance();
	}

}
