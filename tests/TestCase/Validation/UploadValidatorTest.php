<?php
namespace Burzum\FileStorage\Test\TestCase\Model\Table;

use Burzum\FileStorage\TestSuite\FileStorageTestCase;
use Burzum\FileStorage\Validation\UploadValidator;
use Cake\Core\Plugin;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;

/**
 * Upload Validator Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class UploadValidatorTest extends FileStorageTestCase {

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
		parent::setUp();
		$this->Validator = new UploadValidator();
		$this->fileUpload = [
			'error' => UPLOAD_ERR_OK,
			'size' => filesize($this->fileFixtures . 'titus.jpg'),
			'type' => 'image/jpeg',
			'name' => 'tituts.jpg',
			'tmp_name' => $this->fileFixtures . 'titus.jpg'
		];
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Validator);
	}

/**
 * testMimeType
 *
 * @return void
 */
	public function testFilesize() {
		$this->assertFalse($this->Validator->mimeType($this->fileUpload, ['image/gif']));
	}

/**
 * testMimeType
 *
 * @return void
 */
	public function testMimeType() {
		$this->assertFalse($this->Validator->mimeType($this->fileUpload, ['image/gif']));
		$this->assertTrue($this->Validator->mimeType($this->fileUpload, ['image/jpeg']));
		$this->assertTrue($this->Validator->mimeType($this->fileUpload, ['image/jpeg', 'image/png']));
		$this->assertTrue($this->Validator->mimeType($this->fileUpload, 'image/jpeg'));
	}

/**
 * testImageHeight
 *
 * @return void
 */
	public function testExtension() {
		$this->assertTrue($this->Validator->extension($this->fileUpload, ['jpg', 'gif']));
		$this->assertTrue($this->Validator->extension($this->fileUpload, 'jpg'));
		$this->assertFalse($this->Validator->extension($this->fileUpload, ['png']));
		$this->assertFalse($this->Validator->extension($this->fileUpload, 'png'));
	}

/**
 * testUploadErrors
 *
 * @return void
 */
	public function testUploadErrors() {
		$this->assertTrue($this->Validator->uploadErrors(['error' => UPLOAD_ERR_OK]));
		$this->assertFalse($this->Validator->uploadErrors(['error' => UPLOAD_ERR_INI_SIZE]));
		$this->assertFalse($this->Validator->uploadErrors(['error' => UPLOAD_ERR_FORM_SIZE]));
		$this->assertFalse($this->Validator->uploadErrors(['error' => UPLOAD_ERR_PARTIAL]));
		$this->assertFalse($this->Validator->uploadErrors(['error' => UPLOAD_ERR_NO_FILE], ['allowNoFileError' => false]));
		$this->assertTrue($this->Validator->uploadErrors(['error' => UPLOAD_ERR_NO_FILE], ['allowNoFileError' => true]));
		$this->assertFalse($this->Validator->uploadErrors(['error' => UPLOAD_ERR_NO_TMP_DIR]));
		$this->assertFalse($this->Validator->uploadErrors(['error' => UPLOAD_ERR_CANT_WRITE]));
		$this->assertFalse($this->Validator->uploadErrors(['error' => UPLOAD_ERR_EXTENSION]));
	}

/**
 * testImageHeight
 *
 * @return void
 */
	public function testImageHeight() {
		$this->assertTrue($this->Validator->imageHeight($this->fileUpload, '>', 100));
		$this->assertTrue($this->Validator->imageHeight($this->fileUpload, '<', 2000));
		$this->assertTrue($this->Validator->imageHeight($this->fileUpload, '==', 768));
	}

/**
 * testImageWidth
 *
 * @return void
 */
	public function testImageWidth() {
		$this->assertTrue($this->Validator->imageWidth($this->fileUpload, '>', 100));
		$this->assertTrue($this->Validator->imageWidth($this->fileUpload, '<', 2000));
		$this->assertTrue($this->Validator->imageWidth($this->fileUpload, '==', 512));
	}

/**
 * testIsUploadArray
 *
 * @return void
 */
	public function testIsUploadArray() {
		$upload = $this->fileUpload;
		$this->assertTrue($this->Validator->isUploadArray($upload));
		unset($upload['error']);
		$this->assertFalse($this->Validator->isUploadArray($upload));
	}

}
