<?php

declare(strict_types = 1);

namespace Burzum\FileStorage\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Association\HasOne;
use Cake\ORM\Behavior;
use Laminas\Diactoros\UploadedFile;

/**
 * File Association Behavior.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2020 Florian Krämer
 * @license MIT
 */
class FileAssociationBehavior extends Behavior
{
    /**
     * @var array
     * @inheritDoc
     */
    protected $_defaultConfig = [
        'associations' => [],
    ];

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $class = get_class($this->getTable());
        foreach ($config['associations'] as $association => $assocConfig) {
            $associationObject = $this->getTable()->getAssociation($association);

            $defaults = [
                'replace' => $associationObject instanceof HasOne,
                'model' => substr($class, strrpos($class, '\\') + 1, -5),
                'property' => $this->getTable()->getAssociation($association)->getProperty(),
            ];

            $config['associations'][$association] = $assocConfig + $defaults;
        }

        $this->setConfig('associations', $config['associations']);
    }

    /**
     * @param \Cake\Event\EventInterface $event
     * @param \Cake\Datasource\EntityInterface $entity
     * @param \ArrayObject $options
     *
     * @return void
     */
    public function afterSave(
        EventInterface $event,
        EntityInterface $entity,
        ArrayObject $options
    ): void {
        $associations = $this->getConfig('associations');

        foreach ($associations as $association => $assocConfig) {
            $property = $assocConfig['property'];
            if ($entity->{$property} === null) {
                continue;
            }

            if ($entity->id && $entity->{$property} && $entity->{$property}->file) {
                $file = $entity->{$property}->file;

                $ok = false;
                if (is_array($file) && $file['error'] === UPLOAD_ERR_OK) {
                    $ok = true;
                } elseif ($file instanceof UploadedFile && $file->getError() === UPLOAD_ERR_OK) {
                    $ok = true;
                }

                if (!$ok) {
                    continue;
                }

                if ($assocConfig['replace'] === true) {
                    $this->findAndRemovePreviousFile($entity, $association, $assocConfig);
                }

                $entity->{$property}->set('collection', $assocConfig['collection']);
                $entity->{$property}->set('model', $assocConfig['model']);
                $entity->{$property}->set('foreign_key', $entity->id);

                $this->getTable()->{$association}->saveOrFail($entity->{$property});
            }
        }
    }

    /**
     * @param \Cake\Datasource\EntityInterface $entity
     * @param string $association
     * @param array $assocConfig
     *
     * @return void
     */
    protected function findAndRemovePreviousFile(
        EntityInterface $entity,
        string $association,
        array $assocConfig
    ): void {
        $result = $this->getTable()->{$association}->find()
            ->where([
                'collection' => $assocConfig['collection'],
                'model' => $assocConfig['model'],
                'foreign_key' => $entity->get((string)$this->getTable()->getPrimaryKey()),
                'id !=' => $entity->get($assocConfig['property'])->get((string)$this->getTable()->{$association}->getPrimaryKey()),
            ])
            ->first();

        if ($result) {
            $this->getTable()->{$association}->delete($result);
        }
    }
}
