<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

use Burzum\FileStorage\Lib\StorageManager;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Filesystem\Folder;

/**
 * Local FileStorage Event Listener for the CakePHP FileStorage plugin
 *
 * @author Florian Krämer
 * @author Tomenko Yegeny
 * @license MIT
 */
class LocalListener extends AbstractListener {

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

	public function initialize() {
		parent::initialize();
		$this->pathBuilder('Local', ['legacyPath' => true]);;
	}

/**
 * Implemented Events
 *
 * @return array
 */
	public function implementedEvents() {
		return [
			'FileStorage.afterSave' => [
				'callable' => 'afterSave',
			],
			'FileStorage.afterDelete' => [
				'callable' => 'afterDelete',
			]
		];
	}

/**
 * File removal is handled AFTER the database record was deleted.
 *
 * No need to use an adapter here, just delete the whole folder using cakes Folder class
 *
 * @param Event $event
 * @return void
 */
	public function afterDelete(Event $event) {
		if ($this->_checkEvent($event)) {
			$entity = $event->data['record'];
			$path = $this->pathBuilder()->fullPath($entity);
			if (StorageManager::adapter($entity->adapter)->delete($path)) {
				return true;
			}
			return false;
		}
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
				$filename = $this->pathBuilder->filename($entity);
				$entity['path'] = $this->pathBuilder->path($entity);
				$Storage->write($entity['path'] . $filename, file_get_contents($entity['file']['tmp_name']), true);
				$table->save($entity, array(
					'validate' => false,
					'callbacks' => false
				));
			} catch (Exception $e) {
				$this->log($e->getMessage(), 'file_storage');
			}
		}
	}
}
