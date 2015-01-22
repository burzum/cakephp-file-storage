<?php
namespace Burzum\FileStorage\Model\Table;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Folder;
use Burzum\FileStorage\Lib\FileStorageUtils;

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
		if ($entity->isNew) {
			$Event = new Event('ImageStorage.afterSave', $this, array(
				'created' => $event->data['entity']->isNew,
				'storage' => $this->getStorageAdapter($entity['adapter']),
				'record' => $entity
			));
			$this->getEventManager()->dispatch($Event);
		}
		return true;
	}

/**
 * Get a copy of the actual record before we delete it to have it present in afterDelete
 *
 * @param \Cake\Event\Event $event
 * @param \Burzum\FileStorage\Model\Table\Entity $entity
 * @param array $options
 * @return boolean
 */
	public function beforeDelete(Cake\Event\Event $event, Cake\ORM\Entity $entity) {
		if (!parent::beforeDelete($event, $entity)) {
			return false;
		}

		$Event = new Event('ImageStorage.beforeDelete', $this, array(
			'record' => $this->record,
			'storage' => $this->getStorageAdapter($this->record[$this->alias]['adapter'])
		));
		$this->getEventManager()->dispatch($Event);

		if ($Event->isStopped()) {
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
 * @param \Burzum\FileStorage\Model\Table\Entity $entity
 * @param array $options
 * @return void
 */
	public function afterDelete(Cake\Event\Event $event, Cake\ORM\Entity $entity, $options) {
		$Event = new Event('ImageStorage.afterDelete', $this, array(
			'record' => $entity,
			'storage' => $this->getStorageAdapter($entity['adapter'])
		));
		$this->getEventManager()->dispatch($Event);
		return true;
	}

/**
 * Serializes and then hashes an array of operations that are applied to an image
 *
 * @deprecated Don't use this anymore but FileStorageUtils::hashOperations() instead.
 * @param array $operations
 * @return array
 */
	public function hashOperations($operations) {
		return FileStorageUtils::hashOperations($operations);
	}

/**
 * Generate hashes
 *
 * @deprecated Don't use this anymore but FileStorageUtils::generateHashes() instead.
 * @param string
 * @return void
 */
	public function generateHashes($configPath = 'FileStorage') {
		return FileStorageUtils::generateHashes($configPath);
	}

/**
 * Recursive ksort() implementation
 *
 * @deprecated Don't use this anymore but FileStorageUtils::ksortRecursive() instead.
 * @param array $array
 * @param integer
 * @return void
 * @link https://gist.github.com/601849
 */
	public function ksortRecursive(&$array, $sortFlags = SORT_REGULAR) {
		return FileStorageUtils::ksortRecursive($array, $sortFlags);
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
	public function validateImageSize($check, $options) {
		if (!isset($options['height']) && !isset($options['width'])) {
			throw new \InvalidArgumentException(__d('file_storage', 'Invalid image size validation parameters'));
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

}
