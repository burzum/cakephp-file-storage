<?php
namespace Burzum\FileStorage\Storage\Listener;

use Cake\Validation\Validator;
use RuntimeException;

class ValidationListener {

	public $config = [];

	public function __construct(array $config = []) {
		if (!isset($config['passDefaultValidator'])) {
			$config['passDefaultValidator'] = false;
		}
		if (!isset($config['models'])) {
			$config['model'] = FileStorageTable::class;
		}
		$this->config = $config;
	}

	public function implementedEvents() {
		return [
			'Model.initialize' => 'initialize'
		];
	}

	public function initialize(Event $event) {
		$table = $event->subject();
		if (!$table instanceof $this->config['model']) {
			return;
		}

		$methods = get_class_methods($this);
		foreach ($methods as $method) {
			if (substr($method, -9) === 'Validator') {
				if ($this->config['passDefaultValidator']) {
					$validator = $table->validator('default');
				} else {
					$validator = new Validator();
				}

				$validator = $this->{$method}($validator);
				if (!$validator instanceof Validator) {
					throw new RuntimeException();
				}

				$table->validator(substr($method, 0, -9), $validator);
			}
		}
	}
}
