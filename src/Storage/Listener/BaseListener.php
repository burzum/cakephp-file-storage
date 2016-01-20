<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;

/**
 * Base FileStorage Event Listener for the CakePHP FileStorage plugin
 *
 * @author Florian Krämer
 * @license MIT
 */
class BaseListener extends AbstractListener {

	use ImageProcessingTrait;

	/**
	 * Default settings
	 *
	 * @var array
	 */
	protected $_defaultConfig = [
		'pathBuilder' => 'Base',
		'pathBuilderOptions' => [
			'modelFolder' => true,
		],
		'fileHash' => false,
		'imageProcessing' => false,
	];

	/**
	 * List of adapter classes the event listener can work with.
	 *
	 * It is used in FileStorageEventListenerBase::getAdapterClassName to get
	 * the class, to detect if an event passed to this listener should be
	 * processed or not.
	 *
	 * By default the base listener will NOT check if the listener and it's
	 * path builder configuration is compatible with any provided storage
	 * backend!
	 *
	 * @var array
	 */
	public $_adapterClasses = [];

	/**
	 * Implemented Events
	 *
	 * @return array
	 */
	public function implementedEvents() {
		return array_merge(parent::implementedEvents(), [
			'FileStorage.afterSave' => 'afterSave',
			'FileStorage.afterDelete' => 'afterDelete',
			'ImageStorage.afterSave' => 'afterSave',
			'ImageStorage.afterDelete' => 'afterDelete',
			'ImageVersion.removeVersion' => 'removeImageVersion',
			'ImageVersion.createVersion' => 'createImageVersion',
			'ImageVersion.getVersions' => 'imagePath',
			'FileStorage.ImageHelper.imagePath' => 'imagePath', // deprecated
			'FileStorage.getPath' => 'getPath' // deprecated
		]);
	}

	/**
	 * File removal is handled AFTER the database record was deleted.
	 *
	 * No need to use an adapter here, just delete the whole folder using cakes Folder class
	 *
	 * @param \Cake\Event\Event $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @return void
	 */
	public function afterDelete(Event $event, EntityInterface $entity) {
		if ($this->_checkEvent($event)) {
			$event->result = $this->_deleteFile($event);;
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
				$this->autoProcessImageVersions($entity, 'create');
			}

			$event->result = true;
			$event->stopPropagation();
		}
	}

	/**
	 * Generates the path the image url / path for viewing it in a browser depending on the storage adapter
	 *
	 * @param \Cake\Event\Event $event
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function imagePath(Event $event) {
		$data = $event->data + [
			'image' => null,
			'version' => null,
			'options' => [],
			'pathType' => 'fullPath'
		];

		if ($event->subject() instanceof EntityInterface) {
			$data['image'] = $event->subject();
		}

		$entity = $data['image'];
		$version = $data['version'];
		$options = $data['options'];
		$type = $data['pathType'];

		if (!$entity) {
			throw new \InvalidArgumentException('No image entity provided.');
		}

		$this->_loadImageProcessingFromConfig();
		$event->data['path'] = $event->result = $this->imageVersionPath($entity, $version, $type, $options);
		$event->stopPropagation();
	}

	/**
	 * Removes a specific image version.
	 *
	 * @param \Cake\Event\Event $event
	 * @return void
	 */
	public function removeImageVersion(Event $event) {
		$this->_processImages($event, 'removeImageVersions');
	}

	/**
	 * Creates the versions for an image.
	 *
	 * @param \Cake\Event\Event $event
	 * @return void
	 */
	public function createImageVersion(Event $event) {
		$this->_processImages($event, 'createImageVersions');
	}

	/**
	 * @param \Cake\Event\Event $event
	 * @param string $method
	 * return void
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
	 *
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
