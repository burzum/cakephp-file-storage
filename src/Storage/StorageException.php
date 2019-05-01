<?php
declare(strict_types=1);
namespace Burzum\FileStorage\Storage;

use Cake\Datasource\EntityInterface;
use Exception;

/**
 * Storage Exception
 *
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
class StorageException extends Exception
{
    /**
     * Entity
     *
     * @var \Cake\Datasource\EntityInterface Entity object.
     */
    protected $_entity;

    /**
     * Sets the entity in question
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity object.
     * @return void
     */
    public function setEntity(EntityInterface $entity): void
    {
        $this->_entity = $entity;
    }

    /**
     * Returns the entity.
     *
     * @return \Cake\Datasource\EntityInterface $entity Entity object.
     */
    public function getEntity(): \Cake\Datasource\EntityInterface
    {
        return $this->_entity;
    }
}
