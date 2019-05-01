<?php
declare(strict_types=1);
namespace Burzum\FileStorage\Storage\Listener;

use Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait;
use Burzum\FileStorage\Storage\StorageTrait;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;

class ImageProcessingListener implements EventListenerInterface
{
    use ImageProcessingTrait;
    use PathBuilderTrait;
    use StorageTrait;

    /**
     * Returns a list of events this object is implementing. When the class is registered
     * in an event manager, each individual method will be associated with the respective event.
     * ### Example:
     * ```
     *  public function implementedEvents()
     *  {
     *      return [
     *          'Order.complete' => 'sendEmail',
     *          'Article.afterBuy' => 'decrementInventory',
     *          'User.onRegister' => ['callable' => 'logRegistration', 'priority' => 20, 'passParams' => true]
     *      ];
     *  }
     * ```
     *
     * @return array associative array or event key names pointing to the function
     * that should be called in the object when the respective event is fired
     */
    public function implementedEvents(): array
    {
        return [
            'FileStorage.afterStoreFile' => 'afterStoreFile',
            'FileStorage.afterDeleteFile' => 'afterDeleteFile',
        ];
    }

    public function __construct()
    {
        $this->pathBuilder('Base', [
            'modelFolder' => true,
        ]);
    }

    public function afterStoreFile(Event $event, EntityInterface $entity): void
    {
        $this->loadImageProcessingFromConfig();

        $imageVersions = (array)Configure::read('FileStorage.imageSizes');
        $identifiers = array_keys($imageVersions);

        if (!in_array($entity->get('model'), $identifiers)) {
            return;
        }

        $this->createAllImageVersions($entity, $imageVersions[$entity->get('model')]);
    }

    public function afterDeleteFile(Event $event, EntityInterface $entity): void
    {
        $this->loadImageProcessingFromConfig();

        $imageVersions = (array)Configure::read('FileStorage.imageSizes');
        $identifiers = array_keys($imageVersions);

        if (!in_array($entity->get('model'), $identifiers)) {
            return;
        }

        $this->removeAllImageVersions($entity);
    }
}
