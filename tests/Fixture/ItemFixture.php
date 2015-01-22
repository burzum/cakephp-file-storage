<?php
/**
 * Item Fixture
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class ItemFixture extends CakeTestFixture {

/**
 * Name
 *
 * @var string $name
 */
	public $name = 'Item';

/**
 * Table
 *
 * @var array $table
 */
	public $table = 'items';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'char', 'null' => false, 'length' => 36),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']],
		]
	);

/**
 * Records
 *
 * @var array
 */
	public $records = [
		[
			'id'  => 'item-1',
			'name' => 'Cake',
		],
		[
			'id'  => 'item-2',
			'name' => 'More Cake',
		],
		[
			'id'  => 'item-3',
			'name' => 'A lot Cake',
		],
	];

}
