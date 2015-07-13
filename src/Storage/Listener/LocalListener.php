<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

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

	use ImageProcessingTrait;

/**
 * Default settings
 *
 * @var array
 */
	protected $_defaultConfig = [
		'pathBuilder' => 'BasePath',
		'pathBuilderOptions' => [
			'modelFolder' => true,
		],
		'imageProcessing' => false,
	];

/**
 * List of adapter classes the event listener can work with.
 *
 * It is used in FileStorageEventListenerBase::getAdapterClassName to get the
 * class, to detect if an event passed to this listener should be processed or
 * not. Only events with an adapter class present in this array will be
 * processed.
 *
 * @var array
 */
	public $_adapterClasses = [
		'\Gaufrette\Adapter\Local'
	];

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
 * @param \Cake\Event\Event $event
 * @return void
 */
	public function afterDelete(Event $event) {
		if ($this->_checkEvent($event)) {
			$entity = $event->data['record'];
			$path = $this->pathBuilder()->fullPath($entity);
			try {
				if ($this->storageAdapter($entity->adapter)->delete($path)) {
					if ($this->_config['imageProcessing'] === true) {
						$this->autoProcessImageVersions($entity, 'remove');
					}
					$event->result = true;
					return;
				}
			} catch (\Exception $e) {
				$this->log($e->getMessage());
			}
			$event->result = false;
			$event->stopPropagation();
		}
	}

/**
 * Save the file to the storage backend after the record was created.
 *
 * @param \Cake\Event\Event $event
 * @return void
 */
	public function afterSave(Event $event) {
		if ($this->_checkEvent($event) && $event->data['record']->isNew()) {
			$table = $event->subject();
			$entity = $event->data['record'];

			if (!empty($event->data['fileField'])) {
				$this->config('fileField', $event->data['fileField']);
			}

			if ($this->config('fileHash') !== false) {
				$entity->hash = $this->getFileHash(
					$entity[$this->config('fileField')]['tmp_name'],
					$this->config('fileHash')
				);
			}

			$filename = $this->pathBuilder()->filename($entity);
			$entity['path'] = $this->pathBuilder()->path($entity);

			try {
				$Storage = $this->storageAdapter($entity['adapter']);
				$Storage->write($entity['path'] . $filename, file_get_contents($entity[$this->config('fileField')]['tmp_name']), true);
				$table->save($entity, array(
					'validate' => false,
					'callbacks' => false
				));
				$event->result = true;
			} catch (\Exception $e) {
				$this->log($e->getMessage());
				$event->result = false;
			}

			if ($this->_config['imageProcessing'] === true) {
				$this->autoProcessImageVersions($entity, 'create');
			}

			$event->stopPropagation();
		}
	}
}
