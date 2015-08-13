<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;

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
		'pathBuilder' => 'Local',
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
 * @param \Cake\Datasource\EntityInterface $entity
 * @return void
 */
	public function afterSave(Event $event, EntityInterface $entity) {
		if ($this->_checkEvent($event) && $entity->isNew()) {
			$fileField = $this->config('fileField');

			$entity['hash'] = $this->getHash($entity, $fileField);
			$entity['path'] = $this->pathBuilder()->path($entity);

			if (!$this->_storeFile($entity)) {
				return;
			}

			$event->stopPropagation();
		}
	}
}
