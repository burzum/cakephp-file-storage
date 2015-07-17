<?php
namespace Burzum\FileStorage\Storage;

use \Cake\ORM\Entity;
use \Exception;

class StorageException extends Exception {

	protected $_entity = null;

	public function setEntity(Entity $entity) {
		$this->_entity = $entity;
	}

	public function getEntity() {
		return $this->_entity;
	}
}
