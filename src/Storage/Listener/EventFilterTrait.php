<?php
namespace Burzum\FileStorage\Storage\Listener;

use Cake\Event\Event;

/**
 * Filters events and entities to decide if they should be processed or not by
 * a specific storage adapter.
 *
 * - Filter by subject class name
 * - Filter by the entities model field
 * - Filter by adapter class
 * - Filter by adapter config name
 */
trait EventFilterTrait {

    /**
     * Filter settings
     *
     * @var array
     */
    protected $_eventFilters = [
        'subject' => [],
        'adapterConfig' => [],
        'adapterClass' => [],
        'model' => []
    ];

    public function filterBySubject($subject) {
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

    public function filterByModel(Event $event) {
        if (empty($this->_eventFilters['model'])) {
            return true;
        }
        if (isset($event->data['entity']['adapter']) && in_array($event->data['entity']['adapter'], $this->_eventFilters['model'])) {
            return true;
        }

        return false;
    }

    public function filterByAdaperConfig(Event $event) {
        if (empty($this->_eventFilters['adapterConfig'])) {
            return true;
        }
        if (isset($event->data['entity']['adapter']) && in_array($event->data['entity']['adapter'], $this->_eventFilters['adapterConfig'])) {
            return true;
        }

        return false;
    }

    public function filterByAdapterClass(Event $event) {
        if (empty($this->_eventFilters['adapterClass'])) {
            return true;
        }
        if (isset($event->data['entity']['adapter'])) {
            foreach ($this->_eventFilters['adapterClass'] as $adapterClass) {
                $class = $this->_getAdapterClassFromConfig($event->data['entity']['adapter']);
                if ($class === $adapterClass) {
                    return true;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param \Cake\Event\Event
     * @return bool;
     */
    public function filterEvent(Event $event) {
        return
            $this->filterBySubject($event) &&
            $this->filterByAdaperConfig($event) &&
            $this->filterByAdapterClass($event) &&
            $this->filterByModel($event);
    }

    /**
     * Gets the adapter class name from the adapter config
     *
     * @param string $configName Name of the configuration
     * @return bool|string False if the config is not present
     */
    protected function _getAdapterClassFromConfig($configName) {
        $config = StorageManager::config($configName);
        if (!empty($config['adapterClass'])) {
            return $config['adapterClass'];
        }

        return false;
    }

}
