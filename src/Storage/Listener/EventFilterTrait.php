<?php
declare(strict_types=1);

namespace Burzum\FileStorage\Storage\Listener;

use Burzum\FileStorage\Storage\StorageManager;
use Cake\Event\EventInterface;

/**
 * Filters events and entities to decide if they should be processed or not by
 * a specific storage adapter.
 *
 * - Filter by subject class name
 * - Filter by the entities model field
 * - Filter by adapter class
 * - Filter by adapter config name
 */
trait EventFilterTrait
{
    /**
     * Filter settings
     *
     * @var array
     */
    protected $_eventFilters = [
        'subject' => [],
        'adapterConfig' => [],
        'adapterClass' => [],
        'model' => [],
    ];

    public function filterBySubject($subject)
    {
        if (empty($this->_eventFilters['subject'])) {
            return true;
        }
        foreach ($this->_eventFilters['subject'] as $class) {
            if ($subject instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Cake\Event\EventInterface $event
     * @return bool
     */
    public function filterByModel(EventInterface $event): bool
    {
        $data = $event->getData();
        if (empty($this->_eventFilters['model'])) {
            return true;
        }
        if (isset($data['entity']['adapter']) && in_array($data['entity']['adapter'], $this->_eventFilters['model'])) {
            return true;
        }

        return false;
    }

    /**
     * @param \Cake\Event\EventInterface $event
     * @return bool
     */
    public function filterByAdapterConfig(EventInterface $event): bool
    {
        $data = $event->getData();
        if (empty($this->_eventFilters['adapterConfig'])) {
            return true;
        }
        if (isset($data['entity']['adapter']) && in_array($data['entity']['adapter'], $this->_eventFilters['adapterConfig'])) {
            return true;
        }

        return false;
    }

    /**
     * @param \Cake\Event\EventInterface $event
     * @return bool
     */
    public function filterByAdapterClass(EventInterface $event): bool
    {
        $data = $event->getData();
        if (empty($this->_eventFilters['adapterClass'])) {
            return true;
        }
        if (isset($data['entity']['adapter'])) {
            foreach ($this->_eventFilters['adapterClass'] as $adapterClass) {
                $class = $this->_getAdapterClassFromConfig($data['entity']['adapter']);
                if ($class === $adapterClass) {
                    return true;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param \Cake\Event\EventInterface $event
     * @return bool
     */
    public function filterEvent(EventInterface $event): bool
    {
        return $this->filterBySubject($event) &&
            $this->filterByAdapterConfig($event) &&
            $this->filterByAdapterClass($event) &&
            $this->filterByModel($event);
    }

    /**
     * Gets the adapter class name from the adapter config
     *
     * @param string $configName Name of the configuration
     * @return string|null False if the config is not present
     */
    protected function _getAdapterClassFromConfig(string $configName): ?string
    {
        $config = StorageManager::config($configName);
        if (!empty($config['adapterClass'])) {
            return $config['adapterClass'];
        }

        return null;
    }
}
