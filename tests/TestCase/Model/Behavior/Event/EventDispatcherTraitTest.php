<?php
namespace Burzum\FileStorage\Test\TestCase\Storage;

use Burzum\FileStorage\Model\Behavior\Event\EventDispatcherTrait;
use Cake\TestSuite\TestCase;

class TestEventDispatcherTrait {
	protected $_table = null;
	use EventDispatcherTrait;

	public function __construct($table) {
		$this->_table = $table;
	}
	protected function _dispatchEvent($name, $data = null, $subject = null) {
		return compact(['name', 'data', 'subject']);
	}
}

class EventDispatcherTraitTest extends TestCase {

/**
 * testSetAndGetEntity
 *
 * @return void
 */
	public function testDispatchEvent() {
		$Dispatcher = new TestEventDispatcherTrait('Table goes here.');
		$result = $Dispatcher->dispatchEvent('TestEvent', null, []);
		$expected = [
			'name' => 'TestEvent',
			'data' => [
				'table' => 'Table goes here.'
			],
			'subject' => []
		];
		$this->assertEquals($result, $expected);
	}
}
