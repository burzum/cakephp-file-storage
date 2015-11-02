<?php
namespace Burzum\FileStorage\Event;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Filesystem\Folder;
use Burzum\FileStorage\Lib\StorageManager;

/**
 * Local FileStorage Event Listener for the CakePHP FileStorage plugin
 *
 * @author Florian Krämer
 * @author Tomenko Yegeny
 * @license MIT
 */
class LocalFileStorageListener extends AbstractStorageEventListener {

/**
 * List of adapter classes the event listener can work with
 *
 * It is used in FileStorageEventListenerBase::getAdapterClassName to get the
 * class, to detect if an event passed to this listener should be processed or
 * not. Only events with an adapter class present in this array will be
 * processed.
 *
 * @var array
 */
	public $_adapterClasses = array(
		'\Gaufrette\Adapter\Local'
	);

/**
 * Implemented Events
 *
 * @return array
 */
	public function implementedEvents() {
		return [
			'FileStorage.afterSave' => [
				'callable' => 'afterSave',
				'priority' => 50,
			],
			'FileStorage.afterDelete' => [
				'callable' => 'afterDelete',
				'priority' => 50
			]
		];
	}

/**
 * afterDelete
 *
 * No need to use an adapter here, just delete the whole folder using cakes Folder class
 *
 * @param Event $event
 * @return void
 */
	public function afterDelete(Event $event) {
		if ($this->_checkEvent($event)) {
			$entity = $event->data['record'];
			$storageConfig = StorageManager::config($entity['adapter']);
			$path = $storageConfig['adapterOptions'][0] . $event->data['record']['path'];
			if (is_dir($path)) {
				$Folder = new Folder($path);
				return $Folder->delete();
			}
			return false;
		}
	}

/**
 * Builds the path under which the data gets stored in the storage adapter
 *
 * @param Table $table
 * @param Entity $entity
 * @return string
 */
	public function buildPath($table, $entity) {
		$path = parent::buildPath($table, $entity);
		// Backward compatibility
		return 'files' . DS . $path;
	}

/**
 * afterSave
 *
 * @param Event $event
 * @return void
 */
	public function afterSave(Event $event) {
		if ($this->_checkEvent($event) && $event->data['record']->isNew()) {
			$table = $event->subject();
			$entity = $event->data['record'];
			$Storage = StorageManager::adapter($entity['adapter']);
			try {
				$filename = $this->buildFileName($table, $entity);
				$entity['path'] = $this->buildPath($table, $entity);
				$Storage->write($entity['path'] . $filename, file_get_contents($entity['file']['tmp_name']), true);
				$table->save($entity, array(
					'validate' => false,
					'callbacks' => false
				));
			} catch (Exception $e) {
				$this->log($e->getMessage());
			}
		}
	}
}
