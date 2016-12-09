<?php
namespace Burzum\FileStorage\Storage;

use Cake\Datasource\EntityInterface;
use Exception;

/**
 * Storage Exception
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
class StorageException extends Exception {

	/**
	 * Entity
	 *
	 * @var \Cake\Datasource\EntityInterface $entity Entity object.
	 */
	protected $_entity;

	/**
	 * Sets the entity in question
	 *
	 * @param \Cake\Datasource\EntityInterface $entity Entity object.
	 * @return void
	 */
	public function setEntity(EntityInterface $entity) {
		$this->_entity = $entity;
	}

	/**
	 * Returns the entity.
	 *
	 * @return \Cake\Datasource\EntityInterface $entity Entity object.
	 */
	public function getEntity() {
		return $this->_entity;
	}
}
