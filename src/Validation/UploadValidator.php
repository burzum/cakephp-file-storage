<?php
namespace Burzum\FileStorage\Validation;

use Cake\Filesystem\File;
use Cake\I18n\Number;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use InvalidArgumentException;

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
	 * @var int
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
	 * @return bool Returns TRUE if the file named by filename was uploaded via HTTP POST.
	 */
	public function isUploadedFile($value) {
		return is_uploaded_file($value['tmp_name']);
	}

	/**
	 * Validates that a set field / property is a valid upload array.
	 *
	 * @deprecated Use \Cake\Utility\Validation::uploadedFile() instead.
	 * @param mixed $value
	 * @return bool
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
	 * Validates the error value that comes with the file input file.
	 *
	 * @param array $value
	 * @param array $options.
	 * @return bool True on success, if false the error message is set to the models field and also set in $this->_uploadError
	 */
	public function uploadErrors($value, $options = []) {
		$defaults = [
			'allowNoFileError' => true
		];
		if (is_array($options)) {
			$options = array_merge($defaults, $options);
		} else {
			$options = $defaults;
		}
		if (isset($value['error']) && ($value['error'] !== null)) {
			switch ($value['error']) {
				case UPLOAD_ERR_OK:
					return true;
				case UPLOAD_ERR_INI_SIZE:
					$this->_uploadError = __d('file_storage', 'The uploaded file exceeds limit of %s.', Number::toReadableSize(ini_get('upload_max_filesize')));
					return false;
				case UPLOAD_ERR_FORM_SIZE:
					$this->_uploadError = __d('file_storage', 'The uploaded file is to big, please choose a smaller file or try to compress it.');
					return false;
				case UPLOAD_ERR_PARTIAL:
					$this->_uploadError = __d('file_storage', 'The uploaded file was only partially uploaded.');
					return false;
				case UPLOAD_ERR_NO_FILE:
					if ($options['allowNoFileError'] === false) {
						$this->_uploadError = __d('file_storage', 'No file was uploaded.');
						return false;
					}
					return true;
				case UPLOAD_ERR_NO_TMP_DIR:
					$this->_uploadError = __d('file_storage', 'The remote server has no temporary folder for file uploads. Please contact the site admin.');
					return false;
				case UPLOAD_ERR_CANT_WRITE:
					$this->_uploadError = __d('file_storage', 'Failed to write file to disk. Please contact the site admin.');
					return false;
				case UPLOAD_ERR_EXTENSION:
					$this->_uploadError = __d('file_storage', 'File upload stopped by extension. Please contact the site admin.');
					return false;
				default:
					$this->_uploadError = __d('file_storage', 'Unknown File Error. Please contact the site admin.');
					return false;
			}
			return false;
		}
		$this->_uploadError = '';
		return true;
	}

}
