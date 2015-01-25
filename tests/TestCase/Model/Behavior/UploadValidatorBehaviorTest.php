<?php
namespace Burzum\FileStorage\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\ORM\Table;
use Cake\Core\Plugin;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;

/**
 * Upload Validator Behavior Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2014 Florian Krämer
 * @license MIT
 */

/**
 * VoidUploadModel
 */
class VoidUploadModel extends Table {

	/**
	 * name property
	 *
	 * @var string 'TheVoid'
	 */
	public $name = 'VoidUploadModel';

	/**
	 * useTable property
	 *
	 * @var bool false
	 */
	public $useTable = false;

	/**
	 * Initialize
	 *
	 * @param array $config
	 * @return void
	 */
		public function initialize(array $config) {
			parent::initialize($config);
			$this->addBehavior('Burzum/FileStorage.UploadValidator', [
				'localFile' => true
			]);
		}
}

/**
 * UploadValidatorBehaviorTest
 */
class UploadValidatorBehaviorTest extends FileStorageTestCase {

/**
 * Holds the instance of the table
 *
 * @var mixed
 */
	public $Article = null;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [];

/**
 * startTest
 *
 * @return void
 */
	public function setUp() {
		$this->Table = new VoidUploadModel();
//		$this->Table->addBehavior('Burzum/FileStorage.UploadValidator', [
//			'localFile' => true
//		]);
		$this->FileUpload = $this->Table->behaviors()->UploadValidator;
		$this->testFilePath = Plugin::path('Burzum/FileStorage') . 'Test' . DS . 'Fixture' . DS . 'File' . DS;
	}

/**
 * endTest
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Table);
		TableRegistry::clear();
	}

/**
 * configureUploadValidation
 *
 * @return void
 */
	public function testConfigureUploadValidation() {
		$this->Table->configureUploadValidation();
	}

/**
 * testRemoveUploadValidationRules
 *
 * @return void
 */
	public function testRemoveUploadValidationRules() {
		$this->Table->removeUploadValidationRules();
	}

}
