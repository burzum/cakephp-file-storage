<?php
namespace Burzum\FileStorage\Model\Entity;

use Cake\ORM\Entity;

/**
 * FileStorage Entity.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class FileStorage extends Entity {

/**
 * Fields that can be mass assigned using newEntity() or patchEntity().
 *
 * @var array
 */
	protected $_accessible = [
		'id' => true,
		'user_id' => true,
		'foreign_key' => true,
		'model' => true,
		'file' => true,
		'filename' => true,
		'filesize' => true,
		'mime_type' => true,
		'extension' => true,
		'hash' => true,
		'path' => true,
		'adapter' => true,
		'created' => true,
		'modified' => true,
		'file' => true,
	];

}
