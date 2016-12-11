<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\Listener;

use Burzum\FileStorage\Storage\Listener\AbstractListener;
use Cake\TestSuite\TestCase;

class TestAbstractListener extends AbstractListener {

	public function implementedEvents() {
		return [];
	}

}

class AbstractListenerTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.Burzum\FileStorage.FileStorage'
	];

	/**
	 * testPathBuilder
	 *
	 * @return void
	 */
	public function testPathBuilder() {
		$Listener = new TestAbstractListener([
			'pathBuilder' => 'Base'
		]);
		$result = $Listener->pathBuilder();
		$this->assertInstanceOf('\Burzum\FileStorage\Storage\PathBuilder\BasePathBuilder', $result);
	}

}
