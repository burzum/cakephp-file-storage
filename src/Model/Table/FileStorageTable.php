<?php
namespace Burzum\FileStorage\Model\Table;

use Cake\ORM\Table;

/**
 * FileStorageTable
 *
 * Records in this table act as a reference to the real location of the stored
 * file data. All information of a row can be used to build a path to the file.
 * So the data in this table is pretty important.
 *
 * The reason for keeping all file references in this table is simply separation
 * of concerns: We separate the files from the other modules of the application
 * and threat them centralized and all the same.
 *
 * The actual storing and removing of the file data is handled by the Storage
 * Behavior that is attached to this table object.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
class FileStorageTable extends Table {

	/**
	 * Initialize
	 *
	 * @param array $config
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('file_storage');
		$this->setPrimaryKey('id');
		$this->setDisplayField('filename');

		$this->addBehavior('Timestamp');
		$this->addBehavior('Burzum/FileStorage.FileStorage');
	}

}
