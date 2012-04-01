<?php
/**
 * FileStorage
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class FileStorage extends AppModel {
/**
 * Name
 *
 * @var string
 */
	public $name = 'FileStorage';

/**
 * Table name
 *
 * @var string
 */
	public $useTable = 'file_storage';

/**
 * Displayfield
 *
 * @var string
 */
	public $displayField = 'filename';

/**
 * Adapters
 *
 * @var array
 */
	public $adapters = array(
		'Local' => array(
			'adapterOptions' => array(),
			'adapterClass' => '\Gaufrette\Adapter\Local',
			'class' => '\Gaufrette\Filesystem'));

/**
 * Constructor
 *
 * @param 
 * @param 
 * @param 
 * @return void
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$adapterConfig = Configure::read('FileStorage.adapter');
		if (is_array($adapterConfig)) {
			$this->adapters = Set::merge($this->adapters, $adapterConfig);
		}
	}

/**
 * Renews the FileUpload behavior with a new configuration
 *
 * @param array $options
 * @return void
 */
	public function configureFileUpload($options = array()) {
		$this->Behaviors->unload('FileUpload');
		$this->Behaviors->load('FileUpload', $options);
	}

/**
 * StorageAdapter
 *
 * @param mixed $adapterName string or array
 * @param boolean $renewObject
 */
	public function storageAdapter($adapterName, $renewObject = false) {
		if (is_string($adapterName)) {
			if (!empty($this->adapters[$adapterName])) {
				$adapter = $this->adapters[$adapterName];
			} else {
				throw new RuntimeException(__('Invalid Adapter %s', $adapterName));
			}

			if (!empty($this->adapters[$adapterName]['object']) && $renewObject === false) {
				return $this->adapters[$adapterName]['object'];
			}
		}

		$class = $adapter['adapterClass'];
		$rc = new ReflectionClass($class);
		$adapterObject = $rc->newInstanceArgs($adapter['adapterOptions']);
		$engineObject = new $adapter['class']($adapterObject);
		$this->adapters[$adapterName]['object'] = $engineObject;
		return $this->adapters[$adapterName]['object'];
	}

}