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
 */
	public function storageAdapter($adapter) {
		if (is_string($adapter)) {
			if (!empty($this->adapters[$adapter])) {
				$adapter = $this->adapters[$adapter];
			} else {
				throw new RuntimeException(__('Invalid Adapter'));
			}
		}

		$adapterObject = call_user_func_array(array($adapter['adapterClass'], '__construct'), $adapter['adapterOptions']);
		return new $adapter['class']($adapterObject);
	}

}