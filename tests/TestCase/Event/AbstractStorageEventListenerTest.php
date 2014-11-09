<?php
namespace Burzum\FileStorage\Test\TestCase\Event;

use Cake\ORM\TableRegistry;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Model\Table\FileStorageTable;

/**
 * AbstractStorageEventListenerTest Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2014 Florian Krämer
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

}
