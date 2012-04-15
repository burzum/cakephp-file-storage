<?php
/**
 * UploadValidatorBehaviorTest file
 *
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/view/1196/Testing>
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/view/1196/Testing CakePHP(tm) Tests
 * @package       Cake.Test.Case.Model.Behavior
 * @since         CakePHP(tm) v 2.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Model', 'Model');
App::uses('AppModel', 'Model');
App::uses('UploadValidatorBehavior', 'FileStorage.Model\Behavior');
require_once dirname(dirname(__FILE__)) . DS . 'models.php';

/**
 * UploadValidatorBehaviorTest class
 *
 * @package       Cake.Test.Case.Model.Behavior
 */
class UploadValidatorBehaviorTest extends CakeTestCase {

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
		$this->Model = new TheVoid();
		$this->Model->Behaviors->load('FileStorage.UploadValidator', array(
			'localFile' => true));
		$this->FileUpload = $this->Model->Behaviors->UploadValidator;
		$this->testFilePath = WEBROOT_DIR . DS . 'img' . DS;
	}

/**
 * endTest
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Model);
		ClassRegistry::flush();
	}

/**
 * testValidateMime
 *
 * @return void
 */
	public function testValidateUploadExtension() {
		$this->Model->Behaviors->unload('FileStorage.UploadValidator');
		$this->Model->Behaviors->load('FileStorage.UploadValidator', array(
			'localFile' => true,
			'allowedExtensions' => array('png')));
		$this->Model->data[$this->Model->alias]['file']['name'] = $this->testFilePath . 'cake.icon.jpg';
		$this->assertFalse($this->Model->validateUploadExtension());

		$this->Model->data[$this->Model->alias]['file']['name'] = $this->testFilePath . 'cake.icon.png';
		$this->assertTrue($this->Model->validateUploadExtension());
	}

/**
 * testValidateMime
 *
 * @return void
 */
	public function testValidateMime() {
		$this->Model->data[$this->Model->alias]['file']['tmp_name'] = $this->testFilePath . 'cake.icon.png';
		$this->assertFalse($this->Model->validateAllowedMimeTypes(array('application/json')));

		$this->Model->data[$this->Model->alias]['file']['tmp_name'] = $this->testFilePath . 'cake.icon.png';
		$this->assertTrue($this->Model->validateAllowedMimeTypes(array('image/png')));
	}

/**
 * testBeforeValidate
 *
 * @return void
 */
	public function testBeforeValidate() {
		$post = array(
			$this->Model->alias => array(
				'file' => array(
					'name' => 'cake.power.gif',
					'type' => 'image/gif',
					'tmp_name' => $this->testFilePath . 'cake.icon.png',
					'error' => 0,
					'size' => 1212)));

		$post[$this->Model->alias]['file']['error'] = 1;
		$this->Model->data = $post;
		$this->assertFalse($this->FileUpload->beforeValidate($this->Model));
		$this->assertTrue(isset($this->Model->validationErrors['file']));
		unset($this->Model->validationErrors['file']);

		$post[$this->Model->alias]['file']['error'] = 2;
		$this->Model->data = $post;
		$this->assertFalse($this->FileUpload->beforeValidate($this->Model));
		$this->assertTrue(isset($this->Model->validationErrors['file']));
		unset($this->Model->validationErrors['file']);

		$post[$this->Model->alias]['file']['error'] = 3;
		$this->Model->data = $post;
		$this->assertFalse($this->FileUpload->beforeValidate($this->Model));
		$this->assertTrue(isset($this->Model->validationErrors['file']));
		unset($this->Model->validationErrors['file']);

		$post[$this->Model->alias]['file']['error'] = 4;
		$this->Model->data = $post;
		$this->assertFalse($this->FileUpload->beforeValidate($this->Model));
		$this->assertTrue(isset($this->Model->validationErrors['file']));
		unset($this->Model->validationErrors['file']);

		$post[$this->Model->alias]['file']['error'] = 6;
		$this->Model->data = $post;
		$this->assertFalse($this->FileUpload->beforeValidate($this->Model));
		$this->assertTrue(isset($this->Model->validationErrors['file']));
		unset($this->Model->validationErrors['file']);

		$post[$this->Model->alias]['file']['error'] = 7;
		$this->Model->data = $post;
		$this->assertFalse($this->FileUpload->beforeValidate($this->Model));
		$this->assertTrue(isset($this->Model->validationErrors['file']));
		unset($this->Model->validationErrors['file']);

		$post[$this->Model->alias]['file']['error'] = 8;
		$this->Model->data = $post;
		$this->assertFalse($this->FileUpload->beforeValidate($this->Model));
		$this->assertTrue(isset($this->Model->validationErrors['file']));
		unset($this->Model->validationErrors['file']);

		$post[$this->Model->alias]['file']['error'] = 8;
		$this->Model->data = $post;
		$this->assertFalse($this->FileUpload->beforeValidate($this->Model));
		$this->assertTrue(isset($this->Model->validationErrors['file']));
		unset($this->Model->validationErrors['file']);

		$post[$this->Model->alias]['file']['error'] = 42; // Unknow code
		$this->Model->data = $post;
		$this->assertFalse($this->FileUpload->beforeValidate($this->Model));
		$this->assertTrue(isset($this->Model->validationErrors['file']));
		unset($this->Model->validationErrors['file']);

		$post[$this->Model->alias]['file']['error'] = 0;
		$this->Model->data = $post;
		$this->assertTrue($this->FileUpload->beforeValidate($this->Model));

		$post[$this->Model->alias]['file']['error'] = null;
		$this->Model->data = $post;
		$this->assertTrue($this->FileUpload->beforeValidate($this->Model));

		// Test errors
		$this->Model->data = $post;
		$this->FileUpload->setup($this->Model, array('localFile' => false));
		$this->assertFalse($this->FileUpload->beforeValidate($this->Model));
		$this->assertTrue(isset($this->Model->validationErrors['file']));
		unset($this->Model->validationErrors['file']);

		$this->Model->data = $post;
		$this->FileUpload->setup($this->Model, array('localFile' => true, 'allowedMime' => array('jpg')));
		$this->assertFalse($this->FileUpload->beforeValidate($this->Model));
		$this->assertTrue(isset($this->Model->validationErrors['file']));
		unset($this->Model->validationErrors['file']);
	}

}
