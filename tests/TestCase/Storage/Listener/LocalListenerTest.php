<?php
namespace Burzum\FileStorage\Test\TestCase\Storage\Listener;

use Burzum\FileStorage\Storage\Listener\AbstractListener;
use Cake\Core\InstanceConfigTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class LocalListenerTest extends TestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Burzum\FileStorage.FileStorage'
	);

	public function testAfterSave() {

	}

}
