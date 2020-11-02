<?php

use Migrations\AbstractMigration;

/**
 * AddingVariantsAndMetadataFields
 */
class AddingVariantsAndMetadataFields extends AbstractMigration
{
	/**
	 * Change Method.
	 *
	 * More information on this method is available here:
	 * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
	 *
	 * @return void
	 */
	public function change()
	{
		$this->table('file_storage')
			->addColumn('variants', 'json', ['null' => true])
			->addColumn('metadata', 'json', ['null' => true])
			->update();
	}
}
