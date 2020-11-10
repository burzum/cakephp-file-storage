<?php

declare(strict_types = 1);

namespace Burzum\FileStorage\Model\Behavior;

use App\Storage\Identifiers;
use ArrayObject;
use Burzum\FileStorage\FileStorage\DataTransformer;
use Burzum\FileStorage\FileStorage\DataTransformerInterface;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use League\Flysystem\AdapterInterface;
use Phauthentic\Infrastructure\Storage\FileInterface;
use Phauthentic\Infrastructure\Storage\FileStorage;
use Phauthentic\Infrastructure\Storage\Processor\ProcessorInterface;
use RuntimeException;
use Throwable;

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
     * @inheritdoc
     */
    protected $_defaultConfig = [
        'associations' => []
    ];

    /**
     * @inheritdoc
     */
    public function initialize(array $config): void
    {
        $class = get_class($this->getTable());
        foreach ($config['associations'] as $association => &$assocConfig) {
            $defaults = [
                'overrideable' => false,
                'model' => substr($class, strrpos($class, '\\') + 1),
                'property' => $this->getTable()->getAssociation($association)->getProperty()
            ];

            $assoConfig += $assoConfig;
        }

        parent::initialize($config);
    }

    /**
     * @param \Cake\Event\EventInterface $event
     * @param \App\Model\Entity\Event $entity
     * @param \ArrayObject $options
     *
     * @return void
     */
    public function afterSave(
        EventInterface $event,
        EntityInterface $entity,
        ArrayObject $options
    ): void {
        $associations = $this->getConfig('assocations');

        foreach ($associations as $association => $assocConfig) {
            $property = $assocConfig['property'];
            if ($entity->{$property} === null) {
                continue;
            }

            if ($entity->id && $entity->{$property} && $entity->{$property}->file->getError() === UPLOAD_ERR_OK) {
                if ($assocConfig['overrideable'] === true) {
                    $this->findAndRemovePreviousFile($entity, $association, $assocConfig);
                }

                $entity->{$property}->set('collection', $assocConfig['collection']);
                $entity->{$property}->set('model', $assocConfig['model']);
                $entity->{$property}->set('foreign_key', $entity->id);

                $this->{$association}->saveOrFail($entity->{$property});
            }
        }
    }

    /**
     * @param \Cake\Event\EventInterface $event
     * @param string $association
     * @param array $assocConfig
     * @return void
     */
    protected function findAndRemovePreviousFile(
        EntityInterface $entity,
        string $association,
        array $assocConfig
    ): void {
        $result = $this->{$association}->find()
            ->where([
                'collection' => $assocConfig['collection'],
                'model' => $assocConfig['model'],
                'foreign_key' => $entity->get((string)$this->getTable()->getPrimaryKey())
            ]);

        if ($result) {
            $this->{$association}->delete($result);
        }
    }
}
