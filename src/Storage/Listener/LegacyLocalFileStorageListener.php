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
		]
	];

/**
 * Save the file to the storage backend after the record was created.
 *
 * @param \Cake\Event\Event $event
 * @return void
 */
	public function afterSave(Event $event) {
		if ($this->_checkEvent($event) && $event->data['record']->isNew()) {
			if ($this->_checkEvent($event) && $event->data['record']->isNew()) {
				$entity = $event->data['record'];
				$fileField = $this->config('fileField');

				$this->entity['hash'] = $this->getHash($entity, $fileField);
				$entity['path'] = $this->pathBuilder()->path($entity);

				if (!$this->_storeFile($entity)) {
					return;
				}

				$event->stopPropagation();
			}
		}
	}
}
