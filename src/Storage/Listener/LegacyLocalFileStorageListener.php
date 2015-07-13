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
class LegacyLocalFileStorageListener extends LocalListener {

/**
 * Default settings
 *
 * @var array
 */
	protected $_defaultConfig = [
		'pathBuilder' => 'BasePath',
		'pathBuilderOptions' => [
			'pathPrefix' => 'files',
			'modelFolder' => false,
			'preserveFilename' => true,
			'randomPath' => 'crc32'
		],
		'imageProcessing' => false,
	];

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
			$fileField = $this->config('fileField');

			if ($this->config('fileHash') !== false) {
				$entity->hash = $this->getFileHash(
					$entity[]['tmp_name'],
					$this->config('fileHash')
				);
			}

			$entity['path'] = $this->pathBuilder()->path($entity);

			try {
				$Storage = $this->storageAdapter($entity['adapter']);
				$Storage->write($entity['path'], file_get_contents($entity[$fileField]['tmp_name']), true);
				$table->save($entity, array(
					'checkRules' => false
				));
				$event->result = true;
			} catch (\Exception $e) {
				$this->log($e->getMessage());
				$event->result = false;
				return;
			}

			if ($this->_config['imageProcessing'] === true) {
				$this->autoProcessImageVersions($entity, 'create');
			}

			$event->result = true;
			$event->stopPropagation();
		}
	}
}
