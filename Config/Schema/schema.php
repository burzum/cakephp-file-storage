<?php 
/**
 * FileStorage
 *
 * @author Florian Kr�mer
 * @copyright 2012 Florian Kr�mer
 * @license MIT
 */
class FileStorageSchema extends CakeSchema {
/**
 * Name
 *
 * @var string
 */
	public $name = 'FileStorage';

/**
 * Before callback
 *
 * @param string Event
 * @return boolean
 */
	public function before($event = array()) {
		if (Configure::read('FileStorage.schema.useIntegers') === true) {
			$this->file_storage['id']['type'] = 'integer';
			$this->file_storage['id']['length'] = 10;
			$this->file_storage['foreign_key']['type'] = 'integer';
			$this->file_storage['foreign_key']['length'] = 10;
		}
		return true;
	}

/**
 * After callback
 *
 * @param string Event
 * @return boolean
 */
	public function after($event = array()) {
		return true;
	}

/**
 * Schema for file storage table
 *
 * @var array
 */
	public $file_storage = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'user_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'foreign_key' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'model' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 64),
		'filename' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'filesize' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 16),
		'mime_type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'extension' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 5),
		'hash' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 64),
		'path' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'adapter' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32, 'comment' => 'Gaufrette Storage Adapter Class'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
	);

}
