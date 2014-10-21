<?php
namespace FileStorage\Model\Behavior;

use \Cake\ORM\Behavior;
use \Cake\Utility\File;
use \Cake\Utility\Number;

/**
 * Upload Validation Behavior
 *
 * This behavior will validate uploaded files, nothing more, it won't take care of storage.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2014 Florian Krämer
 * @license MIT
 */
class UploadValidatorBehavior extends Behavior {

/**
 * Default settings array
 *
 * @var array
 */
	protected $_defaultSettings = array(
		'fileField' => 'file',
		'validate' => true,
		'allowNoFileError' => true,
		'allowedMime' => null,
		'allowedExtensions' => null,
		'localFile' => false
	);

/**
 * Error message
 *
 * If something fails this is populated with an error message that can be passed to the view
 *
 * @var string
 */
	public $uploadError = null;

/**
 * Behavior setup
 *
 * Merge settings with default config, then it is checking if the target directory
 * exists and if it is writeable. It will throw an error if one of both fails.
 *
 * @param array $settings
 * @throws InvalidArgumentException
 * @return void
 */
	public function setup($settings = array()) {
		if (!is_array($settings)) {
			throw new InvalidArgumentException(__d('file_storage', 'Settings must be passed as array!'));
		}

		$this->settings[$this->_table->alias()] = array_merge($this->_defaults, $settings);
	}

/**
 * Before validation callback
 *
 * Check if the file is really an uploaded file and run custom checks for file 
 * extensions and / or mime type if configured to do so.
 *
 * @param array $options
 * @return boolean True on success
 */
	public function beforeValidate($options = array()) {
		extract($this->settings[$this->_table->alias()]);
		if ($validate === true && isset($this->_table->data[$this->_table->alias()][$fileField]) && is_array($this->_table->data[$this->_table->alias()][$fileField])) {

			if ($this->_table->validateUploadError($this->_table->data[$this->_table->alias()][$fileField]['error']) === false) {
				$this->_table->validationErrors[$fileField] = array($this->uploadError);
				return false;
			}

			if (!empty($this->_table->data[$this->_table->alias()][$fileField])) {
				if (empty($localFile) && !is_uploaded_file($this->_table->data[$this->_table->alias()][$fileField]['tmp_name'])) {
					$this->uploadError = __d('file_storage', 'The uploaded file is no valid upload.');
					$this->_table->invalidate($fileField, $this->uploadError);
					return false;
				}
			}

			if (is_array($allowedMime)) {
				if (!$this->validateAllowedMimeTypes($Model, $allowedMime)) {
					return false;
				}
			}

			if (is_array($allowedExtensions)) {
				if (!$this->validateUploadExtension($Model, $allowedExtensions)) {
					return false;
				}
			}
		}
		return true;
	}

/**
 * Validates the extension
 *
 * @param $validExtensions
 * @return boolean True if the extension is allowed
 */
	public function validateUploadExtension($validExtensions) {
		extract($this->settings[$this->_table->alias()]);
		$extension = $this->fileExtension($Model, $this->_table->data[$this->_table->alias()][$fileField]['name'], false);

		if (!in_array(strtolower($extension), $validExtensions)) {
			$this->uploadError = __d('file_storage', 'You are not allowed to upload files of this type.');
			$this->_table->invalidate($fileField, $this->uploadError);
			return false;
		}
		return true;
	}

/**
 * Validates if the mime type of an uploaded file is allowed
 *
 * @param array Array of allowed mime types
 * @return boolean
 */
	public function validateAllowedMimeTypes($mimeTypes = array()) {
		extract($this->settings[$this->_table->alias()]);
		if (!empty($mimeTypes)) {
			$allowedMime = $mimeTypes;
		}

		$File = new File($this->_table->data[$this->_table->alias()][$fileField]['tmp_name']);
		$mimeType = $File->mime();

		if (!in_array($mimeType, $allowedMime)) {
			$this->uploadError = __d('file_storage', 'You are not allowed to upload files of this type.');
			$this->_table->invalidate($fileField, $this->uploadError);
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
	public function validateUploadError($error = null) {
		if (!is_null($error)) {
			switch ($error) {
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
					if ($this->settings[$this->_table->alias()]['allowNoFileError'] === false) {
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

/**
 * Returns the latest error message
 *
 * @return string
 */
	public function uploadError() {
		return $this->uploadError;
	}

/**
 * Returns an array that matches the structure of a regular upload for a local file
 *
 * @param $file
 * @param string File with path
 * @return array Array that matches the structure of a regular upload
 */
	public function uploadArray($file, $filename = null) {
		$File = new File($file);

		if (empty($fileName)) {
			$filename = basename($file);
		}

		return [
			'name' => $filename,
			'tmp_name' => $file,
			'error' => 0,
			'type' => $File->mime(),
			'size' => $File->size()
		];
	}

/**
 * Return file extension from a given filename
 *
 * @param $name
 * @param bool $realFile
 * @internal param $string
 * @return boolean string or false
 */
	public function fileExtension($name, $realFile = true) {
		if ($realFile) {
			return pathinfo($name, PATHINFO_EXTENSION);
		}
		return substr(strrchr($name,'.'), 1);
	}

}
