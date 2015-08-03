<?php
namespace Burzum\FileStorage\Storage;

use \Cake\Datasource\EntityInterface;
use \Exception;

class StorageException extends Exception {

	protected $_entity = null;

	public function setEntity(EntityInterface $entity) {
		$this->_entity = $entity;
	}

	public function getEntity() {
		return $this->_entity;
	}
}
