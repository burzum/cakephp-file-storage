<?php
/**
 * File Storage Fixture
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class FileStorageFixture extends TestFixture {

	/**
	 * Model name
	 *
	 * @var string
	 */
	public $name = 'FileStorage';

	/**
	 * Table name
	 *
	 * @var string
	 */
	public $table = 'file_storage';

	/**
	 * Fields definition
	 *
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'uuid', 'null' => true, 'default' => null, 'length' => 36],
		'user_id' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 36],
		'foreign_key' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 36],
		'model' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 64],
		'filename' => ['type' => 'string', 'null' => false, 'default' => null],
		'filesize' => ['type' => 'integer', 'null' => true, 'default' => null, 'length' => 16],
		'mime_type' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 32],
		'extension' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 5],
		'hash' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 64],
		'path' => ['type' => 'string', 'null' => true, 'default' => null],
		'adapter' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'comment' => 'Gaufrette Storage Adapter Class'],
		'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']],
		]
	];

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = [
		[
			'id' => 'file-storage-1',
			'user_id' => 'user-1',
			'foreign_key' => 'item-1',
			'model' => 'Item',
			'filename' => 'cake.icon.png',
			'filesize' => '',
			'mime_type' => 'image/png',
			'extension' => 'png',
			'hash' => '',
			'path' => '',
			'adapter' => 'Local',
			'created' => '2012-01-01 12:00:00',
			'modified' => '2012-01-01 12:00:00',
		],
		[
			'id' => 'file-storage-2',
			'user_id' => 'user-1',
			'foreign_key' => 'item-1',
			'model' => 'Item',
			'filename' => 'titus-bienebek-bridle.jpg',
			'filesize' => '',
			'mime_type' => 'image/jpg',
			'extension' => 'jpg',
			'hash' => '',
			'path' => '',
			'adapter' => 'Local',
			'created' => '2012-01-01 12:00:00',
			'modified' => '2012-01-01 12:00:00',
		],
		[
			'id' => 'file-storage-3',
			'user_id' => 'user-1',
			'foreign_key' => 'item-2',
			'model' => 'Item',
			'filename' => 'titus.jpg',
			'filesize' => '335872',
			'mime_type' => 'image/jpg',
			'extension' => 'jpg',
			'hash' => '',
			'path' => '',
			'adapter' => 'Local',
			'created' => '2012-01-01 12:00:00',
			'modified' => '2012-01-01 12:00:00',
		],
		[
			'id' => 'file-storage-4',
			'user_id' => 'user-1',
			'foreign_key' => 'item-4',
			'model' => 'Item',
			'filename' => 'titus.jpg',
			'filesize' => '335872',
			'mime_type' => 'image/jpg',
			'extension' => 'jpg',
			'hash' => '09d82a31',
			'path' => null,
			'adapter' => 'S3',
			'created' => '2012-01-01 12:00:00',
			'modified' => '2012-01-01 12:00:00',
		]
	];

}
