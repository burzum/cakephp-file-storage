<?php
namespace Burzum\FileStorage\Test\TestCase\Event;

use Cake\ORM\TableRegistry;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Model\Table\FileStorageTable;

/**
 * AbstractStorageEventListenerTest Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class AbstractStorageEventListenerTest extends FileStorageTestCase {

/**
 * setUp
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Listener = $this->getMockForAbstractClass('\Burzum\FileStorage\Event\AbstractStorageEventListener');
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Listener);
		TableRegistry::clear();
	}

/**
 * testFsPath
 *
 * @return void
 */
	public function testFsPath() {
		$result = $this->Listener->fsPath('Foobar', 'random-id');
		$this->assertEquals($result, 'Foobar' . DS . '63' . DS . '87' . DS . '12' . DS . 'randomid' . DS);

		$result = $this->Listener->fsPath('Foobar', 'random-id', false);
		$this->assertEquals($result, 'Foobar' . DS . '63' . DS . '87' . DS . '12' . DS);
	}

/**
 * testStripDashes
 *
 * @return void
 */
	public function testStripDashes() {
		$result = $this->Listener->stripDashes('some-string-with-dashes');
		$this->assertEquals($result, 'somestringwithdashes');
	}
}
