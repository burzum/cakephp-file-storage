<?php

use Phinx\Migration\AbstractMigration;

/**
 * https://github.com/burzum/cakephp-file-storage/issues/126
 * http://stackoverflow.com/questions/643690/maximum-mimetype-length-when-storing-type-in-db
 *
 * According to RFC 4288 "Media Type Specifications and Registration Procedures",
 * type (eg. "application") and subtype (eg "vnd...") both can be max 127 characters.
 *
 * Meanwhile, that document has been obsoleted by RFC 6838, which does not alter
 * the maximum size but adds a remark:
 *
 * Also note that while this syntax allows names of up to 127 characters,
 * implementation limits may make such long names problematic. For this reason,
 * <type-name> and <subtype-name> SHOULD be limited to 64 characters.
 */
class FixingMimeTypeField extends AbstractMigration {

	/**
	 * Change Method.
	 * Write your reversible migrations using this method.
	 * More information on writing migrations is available here:
	 * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
	 * The following commands can be used in this method and Phinx will
	 * automatically reverse them when rolling back:
	 *    createTable
	 *    renameTable
	 *    addColumn
	 *    renameColumn
	 *    addIndex
	 *    addForeignKey
	 * Remember to call "create()" or "update()" and NOT "save()" when working
	 * with the Table class.
	 */
	public function change() {
		$this->table('file_storage', ['id' => false, 'primary_key' => 'id'])
			->changeColumn('mime_type', 'string', ['limit' => 128, 'null' => true, 'default' => null])
			->update();
	}
}
