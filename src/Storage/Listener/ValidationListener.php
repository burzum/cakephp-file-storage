<?php
namespace Burzum\FileStorage\Storage\Listener;

use Burzum\FileStorage\Model\Table\FileStorageTable;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use RuntimeException;

class ValidationListener {

    /**
     * Configuration
     *
     * @var array
     */
    public $config = [];

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = []) {
        if (!isset($config['passDefaultValidator'])) {
            $config['passDefaultValidator'] = false;
        }

        if (!isset($config['tableClass'])) {
            $config['tableClass'] = [FileStorageTable::class];
        } elseif (is_string($config['tableClass'])) {
            $config['tableClass'] = [$config['tableClass']];
        }

        $this->config = $config;
    }

    /**
     * Implemented events
     *
     * @return array
     */
    public function implementedEvents() {
        return [
            'Model.initialize' => 'initialize'
        ];
    }

    /**
     * Model initialize event callback
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function initialize(Event $event) {
        $table = $event->getSubject();
        if (!in_array(get_class($table), $this->config['tableClass'])) {
            return;
        }

        $this->_setValidators($table);
    }

    /**
     * Sets the configured validators to the table instance
     *
     * @param \Cake\ORM\Table $table
     * @return void
     */
    protected function _setValidators(Table $table) {
        $methods = get_class_methods($this);

        foreach ($methods as $method) {
            if (substr($method, 0, 10) === 'validation') {
                if ($this->config['passDefaultValidator']) {
                    $validator = $table->getValidator('default');
                } else {
                    $validator = new Validator();
                }

                $validator = $this->{$method}($validator);
                if (!$validator instanceof Validator) {
                    throw new RuntimeException('Object must be of type ' . Validator::class . '. Method ' . $method . ' returned ' . get_class($validator));
                }

                $table->setValidator(lcfirst(substr($method, 10)), $validator);
            }
        }
    }

}
