<?php
namespace Burzum\FileStorage\Storage;

use \Cake\Datasource\EntityInterface;
use \Exception;

/**
 * Storage Exception
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
class StorageException extends Exception {

	protected $_entity = null;

	public function setEntity(EntityInterface $entity) {
		$this->_entity = $entity;
	}

	public function getEntity() {
		return $this->_entity;
	}
}
