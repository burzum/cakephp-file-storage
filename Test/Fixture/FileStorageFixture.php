<?php
class FileStorageFixture extends CakeTestFixture {

/**
 * Model name
 *
 * @var string $model
 */
	public $name = 'FileStorage';

/**
 * Table name
 *
 * @var string $useTable
 */
	public $table = 'file_storage';

/**
 * Fields definition
 *
 * @var array $fields
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'user_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'foreign_key' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'model' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 64),
		'filename' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'filesize' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 16),
		'mime_type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'extension' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 5),
		'hash' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 64),
		'path' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'adapter' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32, 'comment' => 'Gaufrette Storage Adapter Class'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
	);