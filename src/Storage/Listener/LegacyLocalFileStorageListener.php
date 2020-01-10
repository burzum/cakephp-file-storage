<?php
declare(strict_types=1);

/**
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;

/**
 * Local FileStorage Event Listener for the CakePHP FileStorage plugin
 *
 * @author Florian Krämer
 * @author Tomenko Yegeny
 * @license MIT
 *
 * @deprecated This listener class is deprecated
 */
class LegacyLocalFileStorageListener extends LocalListener
{
    /**
     * Default settings
     *
     * @var array
     */
    protected $_defaultConfig = [
        'pathBuilder' => 'Base',
        'pathBuilderOptions' => [
            'pathPrefix' => 'files',
            'modelFolder' => false,
            'preserveFilename' => false,
            'randomPath' => 'sha1',
        ],
        'disableDeprecationWarning' => false,
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if ($this->getConfig('disableDeprecationWarning') !== true) {
            user_error('LegacyLocalFileStorageListener is deprecated! Please migrate to another listener! Or set the disableDeprecationWarning option to true.', E_USER_DEPRECATED);
        }
    }

    /**
     * Save the file to the storage backend after the record was created.
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @return void
     * @throws \Burzum\FileStorage\Storage\StorageException
     */
    public function afterSave(EventInterface $event, EntityInterface $entity): void
    {
        if ($this->_checkEvent($event) && $entity->isNew()) {
            $fileField = $this->getConfig('fileField');

            $entity['hash'] = $this->getFileHash($entity, $fileField);
            $entity['path'] = $this->pathBuilder()->path($entity);

            if (!$this->_storeFile($event)) {
                return;
            }

            $event->stopPropagation();
        }
    }

    public function imageVersionPath(
        EntityInterface $entity,
        ?string $version,
        string $type = 'fullPath',
        array $options = []
    ): string {
        $options += [
            'pathPrefix' => 'images',
        ];

        return parent::imageVersionPath($entity, $version, $type, $options);
    }
}
