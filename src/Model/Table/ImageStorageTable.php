<?php
namespace Burzum\FileStorage\Model\Table;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Folder;
use Cake\Validation\Validation;

/**
 * ImageStorageTable
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
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
 * @param \Burzum\FileStorage\Model\Table\Entity $entity
 * @param array $options
 * @return boolean true on success
 */
	public function beforeSave(\Cake\Event\Event $event,  \Cake\ORM\Entity $entity, $options) {
		if (!parent::beforeSave($event, $entity, $options)) {
			return false;
		}
		$Event = new Event('ImageStorage.beforeSave', $this, array(
			'record' => $entity
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
 * Does not call the parent to avoid that the regular file storage event listener saves the image already
 *
 * @param \Cake\Event\Event $event
 * @param \Burzum\FileStorage\Model\Table\Entity $entity
 * @param array $options
 * @return boolean
 */
	public function afterSave(\Cake\Event\Event $event,  \Cake\ORM\Entity $entity, $options) {
		if ($entity->isNew()) {
			$imageEvent = new Event('ImageStorage.afterSave', $this, [
				'storage' => $this->getStorageAdapter($entity['adapter']),
				'record' => $entity
			]);
			$this->getEventManager()->dispatch($imageEvent);
			$this->deleteOldFileOnSave($entity);
		}
		return true;
	}

/**
 * Get a copy of the actual record before we delete it to have it present in afterDelete
 *
 * @param \Cake\Event\Event $event
 * @param \Cake\ORM\Entity $entity
 * @return boolean
 */
	public function beforeDelete(\Cake\Event\Event $event, \Cake\ORM\Entity $entity) {
		if (!parent::beforeDelete($event, $entity)) {
			return false;
		}

		$imageEvent = new Event('ImageStorage.beforeDelete', $this, [
			'record' => $this->record,
			'storage' => $this->getStorageAdapter($this->record['adapter'])
		]);
		$this->getEventManager()->dispatch($imageEvent);

		if ($imageEvent->isStopped()) {
			return false;
		}

		return true;
	}

/**
 * After the main file was deleted remove the the thumbnails
 *
 * Note that we do not call the parent::afterDelete(), we just want to trigger the ImageStorage.afterDelete event but not the FileStorage.afterDelete at the same time!
 *
 * @param \Cake\Event\Event $event
 * @param \Cake\ORM\Entity $entity
 * @param array $options
 * @return void
 */
	public function afterDelete(\Cake\Event\Event $event, \Cake\ORM\Entity $entity, $options) {
		$imageEvent = new Event('ImageStorage.afterDelete', $this, [
			'record' => $entity,
			'storage' => $this->getStorageAdapter($entity['adapter'])
		]);
		$this->getEventManager()->dispatch($imageEvent);
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
 * @param array $entity An ImageStorage database record
 * @param array $options. Options for the version.
 * @return array A list of versions for this image file. Key is the version, value is the path or URL to that image.
 */
	public function getImageVersions($entity, $options = []) {
		$versions = [];
		$versionData = (array)Configure::read('FileStorage.imageSizes.' . $entity['model']);
		$versionData['original'] = isset($options['originalVersion']) ? $options['originalVersion'] : 'original';
		foreach ($versionData as $version => $data) {
			$hash = Configure::read('FileStorage.imageHashes.' . $entity['model'] . '.' . $version);
			$Event = new Event('ImageVersion.getVersions', $this, [
					'hash' => $hash,
					'image' => $entity,
					'version' => $version,
					'options' => []
				]
			);
			$this->getEventManager()->dispatch($Event);
			if ($Event->isStopped()) {
				$versions[$version] = str_replace('\\', '/', $Event->data['path']);
			}
		}
		return $versions;
	}
}
