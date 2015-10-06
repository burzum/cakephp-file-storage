<?php
namespace Burzum\FileStorage\Model\Table;

use Burzum\FileStorage\Storage\StorageTrait;
use Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait;
use Cake\Log\LogTrait;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Validation\Validation;

/**
 * ImageStorageTable
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 * @deprecated 3.1.0 Use ImageStorageBehavior in your tables instead.
 */
class ImageStorageTable extends FileStorageTable {

/**
 * Name
 *
 * @var string
 */
	public $name = 'ImageStorage';

/**
 * Initialize
 *
 * @param array $config
 * @return void
 */
	public function initialize(array $config) {
		parent::initialize($config);
		$this->addBehavior('Burzum/Imagine.Imagine');
		$this->addBehavior('Burzum/FileStorage.UploadValidator', array(
			'localFile' => false,
			'validate' => true,
			'allowedExtensions' => array(
				'jpg', 'jpeg', 'png', 'gif'
			)
		));
	}

/**
 * beforeSave callback
 *
 * @param \Cake\Event\Event $event
 * @param \Cake\Datasource\EntityInterface $entity
 * @param array $options
 * @return void
 */
	public function beforeSave(Event $event, EntityInterface $entity, $options) {
		if ($event->isStopped()) {
			return;
		}
		$imageEvent = $this->dispatchEvent('ImageStorage.beforeSave', [
			'record' => $entity
		]);
		if ($imageEvent->isStopped()) {
			$event->stopPropagation();
		}
	}

/**
 * afterSave callback
 *
 * Does not call the parent to avoid that the regular file storage event listener saves the image already
 *
 * @param \Cake\Event\Event $event
 * @param \Cake\Datasource\EntityInterface $entity
 * @param array $options
 * @return void
 */
	public function afterSave(Event $event, EntityInterface $entity, $options) {
		if ($entity->isNew()) {
			$this->dispatchEvent('ImageStorage.afterSave', [
				'record' => $entity,
				'storage' => $this->storageAdapter($entity->get('adapter'))
			]);
			$this->deleteOldFileOnSave($entity);
		}
	}

/**
 * Get a copy of the actual record before we delete it to have it present in afterDelete
 *
 * @param \Cake\Event\Event $event
 * @param \Cake\Datasource\EntityInterface $entity
 * @return void
 */
	public function beforeDelete(Event $event, EntityInterface $entity) {
		parent::beforeDelete($event, $entity);
		if ($event->isStopped()) {
			return;
		}

		$imageEvent = $this->dispatchEvent('ImageStorage.beforeDelete', [
			'record' => $this->record,
			'storage' => $this->storageAdapter($this->record['adapter'])
		]);

		if ($imageEvent->isStopped()) {
			$event->stopPropagation();
		}
	}

/**
 * After the main file was deleted remove the the thumbnails
 *
 * Note that we do not call the parent::afterDelete(), we just want to trigger the ImageStorage.afterDelete event but not the FileStorage.afterDelete at the same time!
 *
 * @param \Cake\Event\Event $event
 * @param \Cake\Datasource\EntityInterface $entity
 * @param array $options
 * @return boolean
 */
	public function afterDelete(Event $event, EntityInterface $entity, $options) {
		$this->dispatchEvent('ImageStorage.afterDelete', [
			'record' => $entity,
			'storage' => $this->storageAdapter($entity->get('adapter'))
		]);
		return true;
	}

/**
 * Image size validation method
 *
 * @param mixed $check
 * @param array $options is an array with key width or height and a value of array
 *    with two options, operator and value. For example:
 *    array('height' => array('==', 100)) will only be true if the image has a
 *    height of exactly 100px. See the CakePHP core class and method
 *    Validation::comparison for all operators.
 * @return boolean true
 * @see Validation::comparison()
 * @throws \InvalidArgumentException
 */
	public function validateImageSize($check, array $options = []) {
		if (!isset($options['height']) && !isset($options['width'])) {
			throw new \InvalidArgumentException('Missing image size validation options! You must provide a hight and / or width.');
		}

		if (is_string($check)) {
			$imageFile = $check;
		} else {
			$check = array_values($check);
			$check = $check[0];
			if (is_array($check) && isset($check['tmp_name'])) {
				$imageFile = $check['tmp_name'];
			} else {
				$imageFile = $check;
			}
		}

		$imageSizes = $this->getImageSize($imageFile);

		if (isset($options['height'])) {
			$height = Validation::comparison($imageSizes[1], $options['height'][0], $options['height'][1]);
		} else {
			$height = true;
		}

		if (isset($options['width'])) {
			$width = Validation::comparison($imageSizes[0], $options['width'][0], $options['width'][1]);
		} else {
			$width = true;
		}

		if ($height === false || $width === false) {
			return false;
		}

		return true;
	}

/**
 * Gets a list of image versions for a given record.
 *
 * Use this method to get a list of ALL versions when needed or to cache all the
 * versions somewhere. This method will return all configured versions for an
 * image. For example you could store them serialized along with the file data
 * by adding a "versions" field to the DB table and extend this model.
 *
 * Just in case you're wondering about the event name in the method code: It's
 * called FileStorage.ImageHelper.imagePath there because the event is the same
 * as in the helper. No need to introduce yet another event, the existing event
 * already fulfills the purpose. I might rename this event in the 3.0 version of
 * the plugin to a more generic one.
 *
 * @param \Cake\Datasource\EntityInterface $entity An ImageStorage database record
 * @param array $options Options for the version.
 * @return array A list of versions for this image file. Key is the version, value is the path or URL to that image.
 */
	public function getImageVersions(EntityInterface $entity, $options = []) {
		$versions = [];
		$versionData = (array)Configure::read('FileStorage.imageSizes.' . $entity->get('model'));
		$versionData['original'] = isset($options['originalVersion']) ? $options['originalVersion'] : 'original';
		foreach ($versionData as $version => $data) {
			$hash = Configure::read('FileStorage.imageHashes.' . $entity->get('model') . '.' . $version);
			$event = $this->dispatchEvent('ImageVersion.getVersions', [
					'hash' => $hash,
					'image' => $entity,
					'version' => $version,
					'options' => []
				]
			);
			if ($event->isStopped()) {
				$versions[$version] = str_replace('\\', '/', $event->data['path']);
			}
		}
		return $versions;
	}
}
