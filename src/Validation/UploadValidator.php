<?php
namespace Burzum\FileStorage\Validation;

use Cake\Validation\Validator;

class UploadValidator extends Validator {

	public function extension($value, $field, $context) {
		$extension = pathinfo($value['name'], PATHINFO_EXTENSION);
		if (!in_array(strtolower($extension), $context['extensions'])) {
			return false;
		}
		return true;
	}

	public function mimeType($value, $field, $context) {
		extract($this->_config);
		if (!empty($mimeTypes)) {
			$allowedMime = $mimeTypes;
		}

		$File = new File($value['tmp_name']);
		$mimeType = $File->mime();

		if (!in_array($mimeType, $allowedMime)) {
			return false;
		}
		return true;
	}

/**
 * Valdates the error value that comes with the file input file
 *
 * @param integer Error value from the form input [file_field][error]
 * @return boolean True on success, if false the error message is set to the models field and also set in $this->uploadError
 */
	public function validateUploadError($value, $field, $contex) {
		if (!is_null($value['error'])) {
			switch ($value['error']) {
				case UPLOAD_ERR_OK:
					return true;
				break;
				case UPLOAD_ERR_INI_SIZE:
					$this->uploadError = __d('file_storage', 'The uploaded file exceeds limit of %s.', CakeNumber::toReadableSize(ini_get('upload_max_filesize')));
				break;
				case UPLOAD_ERR_FORM_SIZE:
					$this->uploadError = __d('file_storage', 'The uploaded file is to big, please choose a smaller file or try to compress it.');
				break;
				case UPLOAD_ERR_PARTIAL:
					$this->uploadError = __d('file_storage', 'The uploaded file was only partially uploaded.');
				break;
				case UPLOAD_ERR_NO_FILE:
					if ($this->_config['allowNoFileError'] === false) {
						$this->uploadError = __d('file_storage', 'No file was uploaded.');
						return false;
					}
					return true;
				break;
				case UPLOAD_ERR_NO_TMP_DIR:
					$this->uploadError = __d('file_storage', 'The remote server has no temporary folder for file uploads. Please contact the site admin.');
				break;
				case UPLOAD_ERR_CANT_WRITE:
					$this->uploadError = __d('file_storage', 'Failed to write file to disk. Please contact the site admin.');
				break;
				case UPLOAD_ERR_EXTENSION:
					$this->uploadError = __d('file_storage', 'File upload stopped by extension. Please contact the site admin.');
				break;
				default:
					$this->uploadError = __d('file_storage', 'Unknown File Error. Please contact the site admin.');
				break;
			}
			return false;
		}
		return true;
	}
}
