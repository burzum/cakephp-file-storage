<?php
namespace Burzum\FileStorage\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Cake\ORM\Table;

/**
 * Upload Validator Behavior Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2014 Florian Krämer
 * @license MIT
 */

/**
 * TheVoid class
 *
 * @package       Cake.Test.Case.Model
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
			$this->addBehavior('Burzum/FileStorage.UploadValidator');
		}
}

/**
 * UploadValidatorBehaviorTest class
 *
 * @package       Cake.Test.Case.Model.Behavior
 */
class UploadValidatorBehaviorTest extends FileStorageTestCase {

/**
 * Holds the instance of the model
 *
 * @var mixed
 */
	public $Article = null;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array();

/**
 * startTest
 *
 * @return void
 */
	public function setUp() {
		$this->Model = new VoidUploadModel();
		$this->Model->addBehavior('Burzum/FileStorage.UploadValidator', array(
			'localFile' => true));
		$this->FileUpload = $this->Model->Behaviors->UploadValidator;
		$this->testFilePath = CakePlugin::path('FileStorage') . 'Test' . DS . 'Fixture' . DS . 'File' . DS;
	}

/**
 * endTest
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Model);
		TableRegistry::clear();
	}
}
