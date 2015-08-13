<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

use Burzum\FileStorage\Storage\StorageException;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Psr\Log\LogLevel;

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
		'pathBuilder' => 'Local',
		'pathBuilderOptions' => [
			'modelFolder' => true,
		],
		'fileHash' => false,
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
			'FileStorage.afterSave' => 'afterSave',
			'FileStorage.afterDelete' => 'afterDelete',
			'ImageStorage.afterSave' => 'afterSave',
			'ImageStorage.afterDelete' => 'afterDelete',
			'ImageVersion.removeVersion' => 'removeImageVersion',
			'ImageVersion.createVersion' => 'createImageVersion',
			'ImageVersion.getVersions' => 'imagePath',
			'FileStorage.ImageHelper.imagePath' => 'imagePath' // deprecated
		];
	}

/**
 * File removal is handled AFTER the database record was deleted.
 *
 * No need to use an adapter here, just delete the whole folder using cakes Folder class
 *
 * @param \Cake\Event\Event $event
 * @param \Cake\Datasource\EntityInterface $entity
 * @throws \Burzum\Filestorage\Storage\StorageException
 * @return void
 */
	public function afterDelete(Event $event, EntityInterface $entity) {
		if ($this->_checkEvent($event)) {
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
				$this->log($e->getMessage(), LOG_ERR, ['scope' => ['storage']]);
				throw new StorageException($e->getMessage());
			}
			$event->result = false;
			$event->stopPropagation();
		}
	}

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

			$entity['hash'] = $this->getFileHash($entity, $fileField);
			$entity['path'] = $this->pathBuilder()->fullPath($entity);

			if (!$this->_storeFile($event)) {
				return;
			}

			if ($this->_config['imageProcessing'] === true) {
				$options = isset($event->data['options']) ? $event->data['options'] : [];
				$this->autoProcessImageVersions($entity, 'create', $options);
			}

			$event->stopPropagation();
		}
	}

/**
 * Generates the path the image url / path for viewing it in a browser depending on the storage adapter
 *
 * @param Event $event
 * @throws RuntimeException
 * @return void
 */
	public function imagePath(Event $event) {
		$entity = $event->data['image'];
		$version = $event->data['version'];
		$options = $event->data['options'];
		$type = isset($event->data['pathType']) ? $event->data['pathType'] : 'fullPath';

		$this->_loadImageProcessingFromConfig();
		$event->data['path'] = $this->imageVersionPath($entity, $version, $type, $options);

		$event->stopPropagation();
	}

/**
 * Stores the file in the configured storage backend.
 *
 * @param \Cake\Event\Event $event
 * @throws \Burzum\Filestorage\Storage\StorageException
 * @return boolean
 */
	protected function _storeFile(Event $event) {
		try {
			$fileField = $this->config('fileField');
			$entity = $event->data['record'];
			$Storage = $this->storageAdapter($entity['adapter']);
			$Storage->write($entity['path'], file_get_contents($entity[$fileField]['tmp_name']), true);
			$event->result = $event->data['table']->save($entity, array(
				'checkRules' => false
			));
			return true;
		} catch (\Exception $e) {
			$this->log($e->getMessage(), LogLevel::ERROR, ['scope' => ['storage']]);
			throw new StorageException($e->getMessage());
		}
	}

/**
 *
 */
	public function removeImageVersion(Event $event) {
		$this->_processImages($event, 'removeImageVersions');
	}

/**
 *
 */
	public function createImageVersion(Event $event) {
		$this->_processImages($event, 'createImageVersions');
	}

/**
 *
 */
	protected function _processImages(Event $event, $method) {
		if ($this->config('imageProcessing') !== true) {
			return;
		}

		$versions = $this->_getVersionData($event);
		$options = isset($event->data['options']) ? $event->data['options'] : [];

		$this->_loadImageProcessingFromConfig();
		$event->result = $this->{$method}(
			$event->data['record'],
			$versions,
			$options
		);
	}

/**
 * This method retrieves version names from event data.
 * For backward compatibility version names are resolved from operations data keys because in old
 * ImageProcessingListener operations were required in event data. ImageProcessingTrait need only
 * version names so operations can be read from the config.
 *
 * @param \Cake\Event\Event $event
 * @return array
 */
	protected function _getVersionData($event)
	{
		if (isset($event->data['versions'])) {
			$versions = $event->data['versions'];
		} elseif (isset($event->data['operations'])) {
			$versions = array_keys($event->data['operations']);
		} else {
			$versions = [];
		}

		return $versions;
	}
}
