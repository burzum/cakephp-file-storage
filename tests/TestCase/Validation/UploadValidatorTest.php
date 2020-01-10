<?php
declare(strict_types=1);

namespace Burzum\FileStorage\Test\TestCase\Model\Table;

use Burzum\FileStorage\Test\TestCase\FileStorageTestCase;
use Burzum\FileStorage\Validation\UploadValidator;

/**
 * Upload Validator Test
 *
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
class UploadValidatorTest extends FileStorageTestCase
{
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
    public function setUp(): void
    {
        parent::setUp();
        $this->Validator = new UploadValidator();
        $this->fileUpload = [
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($this->fileFixtures . 'titus.jpg'),
            'type' => 'image/jpeg',
            'name' => 'tituts.jpg',
            'tmp_name' => $this->fileFixtures . 'titus.jpg',
        ];
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->Validator);
    }

    /**
     * testUploadErrors
     *
     * @return void
     */
    public function testUploadErrors()
    {
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
     * testIsUploadArray
     *
     * @return void
     */
    public function testIsUploadArray()
    {
        $upload = $this->fileUpload;
        $this->assertTrue($this->Validator->isUploadArray($upload));
        unset($upload['error']);
        $this->assertFalse($this->Validator->isUploadArray($upload));
    }
}
