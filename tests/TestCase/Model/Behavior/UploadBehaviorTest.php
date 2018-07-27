<?php
namespace Burzum\FileStorage\Test\TestCase\Model\Behavior;

use Burzum\FileStorage\Test\TestCase\FileStorageTestCase;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;

/**
 * UploadBehaviorTest
 */
class UploadBehaviorTest extends FileStorageTestCase {

	/**
	 * Holds the instance of the table
	 *
	 * @var \Cake\ORM\Table|null
	 */
	public $Items = null;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.Burzum\FileStorage.FileStorage',
		'plugin.Burzum\FileStorage.Item'
	];

	/**
	 * startTest
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Items = TableRegistry::getTableLocator()->get('Items');
		$this->Items->addBehavior('Burzum/FileStorage.Upload');
		$this->testFilePath = Plugin::path('Burzum/FileStorage') . 'Test' . DS . 'Fixture' . DS . 'File' . DS;
	}

	/**
	 * endTest
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Items);
		TableRegistry::getTableLocator()->clear();
	}

	/**
	 * testSaveFile
	 *
	 * @return void
	 */
	public function testSaveFile() {
		//$file = $this->fileFixtures . 'titus.jpg';
		//$result = $this->Items->saveFile($file);
		//debug($result);
	}

}
