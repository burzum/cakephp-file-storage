<?php
/**
 * Item Fixture
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ItemFixture extends TestFixture {

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
		'id' => array('type' => 'uuid', 'null' => true, 'default' => NULL, 'length' => 36),
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
