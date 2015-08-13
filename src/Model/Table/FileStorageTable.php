<?php
namespace Burzum\FileStorage\Model\Table;

use Cake\ORM\Table;

/**
 * FileStorageTable
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 * @deprecated 3.1.0 Use ImageStorageBehavior in your tables instead.
 */
class FileStorageTable extends Table {

/**
 * Name
 *
 * @var string
 */
	public $name = 'FileStorage';

/**
 * The record that was deleted
 *
 * This gets set in the beforeDelete() callback so that the data is available
 * in the afterDelete() callback
 *
 * @var array
 */
	public $record = [];

/**
 * Initialize
 *
 * @param array $config
 * @return void
 */
	public function initialize(array $config) {
		parent::initialize($config);
		//$this->addBehavior('Burzum/FileStorage.UploadValidator');
		$this->addBehavior('Burzum/FileStorage.FileStorage');
		$this->addBehavior('Timestamp');
		$this->displayField('filename');
		$this->table('file_storage');
	}

/**
 * Renews the FileUpload behavior with a new configuration
 *
 * @param array $options
 * @return void
 */
	public function configureUploadValidation($options) {
		$this->removeBehavior('Burzum/FileStorage.UploadValidator');
		$this->addBehavior('Burzum/FileStorage.UploadValidator', $options);
	}
}
