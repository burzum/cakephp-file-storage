<?php
namespace Burzum\FileStorage\Validation;

use Cake\Filesystem\File;
use Cake\Validation\Validator;
use Cake\Validation\Validation;
use Cake\I18n\Number;

class UploadValidator extends Validator {

/**
 * Upload error message after validation.
 *
 * @var string
 */
	protected $_uploadError = '';

/**
 * Upload error message after validation.
 *
 * @var string
 */
	protected $_mimeType = '';

/**
 * Upload extension after validation.
 *
 * @var string
 */
	protected $_extension = '';

/**
 * Upload file size after validation.
 *
 * @var string
 */
	protected $_filesize = 0;

/**
 * Upload error message.
 *
 * @var string
 */
	public function __construct() {
		$this->provider('UploadValidator', $this);
	}

/**
 * Checks if the file was uploaded via HTTP POST.
 *
 * Note that calling this function before move_uploaded_file() is not necessary,
 * as it does the exact same checks already. It provides no extra security.
 * Only when you're trying to use an uploaded file for something other than
 * moving it to a new location.
 *
 * @link http://php.net/manual/en/function.is-uploaded-file.php
 * @param array $value
 * @return boolean Returns TRUE if the file named by filename was uploaded via HTTP POST.
 */
	public function isUploadedFile($value) {
		return is_uploaded_file($value['tmp_name']);
	}

/**
 * Validates that a set field / property is a valid upload array.
 *
 * @param mixed $value
 * @return boolean
 */
	public function isUploadArray($value) {
		if (!is_array($value)) {
			return false;
		}
		$requiredKeys = ['name', 'type', 'tmp_name', 'error', 'size'];
		$keys = array_keys($value);
		foreach ($requiredKeys as $key) {
			if (!in_array($key, $keys)) {
				return false;
			}
		}
		return true;
	}

/**
 * Validates the filesize.
 *
 * @param array $value.
 * @param int $size.
 * @param array $context.
 * @param string $operator.
 * @return bool
 */
	public function fileSize($value, $size, $context = null, $operator = '<') {
		$this->_filesize = $value['size'];
		return Validation::fileSize($value, $operator, $size);
	}

/**
 * Validates extensions.
 *
 * @param array $value.
 * @param array $extensions.
 * @return boolean
 */
	public function extension($value, $extensions) {
		if (is_string($extensions)) {
			$extensions = [$extensions];
		}
		foreach ($extensions as &$extension) {
			$extension = strtolower($extension);
		}
		$this->_extension = pathinfo($value['name'], PATHINFO_EXTENSION);
		if (!in_array(strtolower($this->_extension), $extensions)) {
			return false;
		}
		return true;
	}

/**
 * Validates mime types.
 *
 * @param array $value.
 * @param array $mimeTypes.
 * @return boolean
 */
	public function mimeType($value, $mimeTypes) {
		if (is_string($mimeTypes)) {
			$mimeTypes = [$mimeTypes];
		}

		$File = new File($value['tmp_name']);
		$this->_mimeType = $this->_mimeType = $File->mime();

		if (!in_array($this->_mimeType, $mimeTypes)) {
			return false;
		}
		return true;
	}

/**
 * Validates the image size.
 *
 * @param array $value
 * @param array $options
 * @return boolean
 */
	public function imageSize($value, $options) {
		if (!isset($options['height']) && !isset($options['width'])) {
			throw new \InvalidArgumentException(__d('file_storage', 'Invalid image size validation parameters!'));
		}
		list($width, $height, $type, $attr) = getimagesize($value['tmp_name']);
		if (isset($options['height'])) {
			$validHeight = Validation::comparison($height, $options['height'][1], $options['height'][0]);
		}
		if (isset($options['width'])) {
			$validWidth = Validation::comparison($width, $options['width'][1], $options['width'][0]);
		}
		if (isset($validHeight) && isset($validWidth)) {
			return ($validHeight && $validWidth);
		}
		if (isset($validHeight)) {
			return $validHeight;
		}
		if (isset($validWidth)) {
			return $validWidth;
		}
		throw new \InvalidArgumentException('The 2nd argument is missing one or more configuration keyes.');
	}

/**
 * Validates the image width.
 *
 * @param array $value
 * @param string $operator
 * @param integer $width
 * @return boolean
 */
	public function imageWidth($value, $operator, $width) {
		return $this->imageSize($value, [
			'width' => [
				$width,
				$operator
			]
		]);
	}

/**
 * Validates the image width.
 *
 * @param array $value
 * @param string $operator
 * @param integer $height
 * @return boolean
 */
	public function imageHeight($value, $operator, $height) {
		return $this->imageSize($value, [
			'height' => [
				$height,
				$operator
			]
		]);
	}

/**
 * Validates the error value that comes with the file input file.
 *
 * @param array $value
 * @param array $options.
 * @return boolean True on success, if false the error message is set to the models field and also set in $this->_uploadError
 */
	public function uploadErrors($value, $options = array()) {
		$defaults = [
			'allowNoFileError' => true
		];
		if (is_array($options)) {
			$options = array_merge($defaults, $options);
		} else {
			$options = $defaults;
		}
		if (isset($value['error']) && !is_null($value['error'])) {
			switch ($value['error']) {
				case UPLOAD_ERR_OK:
					return true;
				break;
				case UPLOAD_ERR_INI_SIZE:
					$this->_uploadError = __d('file_storage', 'The uploaded file exceeds limit of %s.', Number::toReadableSize(ini_get('upload_max_filesize')));
				break;
				case UPLOAD_ERR_FORM_SIZE:
					$this->_uploadError = __d('file_storage', 'The uploaded file is to big, please choose a smaller file or try to compress it.');
				break;
				case UPLOAD_ERR_PARTIAL:
					$this->_uploadError = __d('file_storage', 'The uploaded file was only partially uploaded.');
				break;
				case UPLOAD_ERR_NO_FILE:
					if ($options['allowNoFileError'] === false) {
						$this->_uploadError = __d('file_storage', 'No file was uploaded.');
						return false;
					}
					return true;
				break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$this->_uploadError = __d('file_storage', 'The remote server has no temporary folder for file uploads. Please contact the site admin.');
				break;
				case UPLOAD_ERR_CANT_WRITE:
					$this->_uploadError = __d('file_storage', 'Failed to write file to disk. Please contact the site admin.');
				break;
				case UPLOAD_ERR_EXTENSION:
					$this->_uploadError = __d('file_storage', 'File upload stopped by extension. Please contact the site admin.');
				break;
				default:
					$this->_uploadError = __d('file_storage', 'Unknown File Error. Please contact the site admin.');
				break;
			}
			return false;
		}
		$this->_uploadError = '';
		return true;
	}
}
