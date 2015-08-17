<?php
namespace Burzum\FileStorage\Model\Behavior;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\ORM\Behavior;
use Cake\Utility\File;
use Cake\Utility\Number;
use Cake\Utility\Hash;

/**
 * Upload Validation Behavior.
 *
 * This behavior will validate uploaded files, nothing more, it won't take care
 * of storage.
 *
 * The behavior is mostly a backward compatibility tool for the 2.x version of
 * the FileStorage plugin and a convenient behavior for setting file upload
 * validation up. You're not forced to use it but I think you need to type a
 * little less code by using it.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class UploadValidatorBehavior extends Behavior {

/**
 * Default settings array
 *
 * @var array
 */
	protected $_defaultConfig = array(
		'fileField' => 'file',
		'validate' => true,
		'validator' => 'default',
		'validateUploadErrors' => true,
		'validateUploadArray' => false,
		'validateFilesize' => false,
		'validateImageSize' => [],
		'allowNoFileError' => true,
		'allowedMime' => [],
		'allowedExtensions' => [],
		'localFile' => false
	);

/**
 * Constructor
 *
 * @param \Cake\ORM\Table $table The table this behavior is attached to.
 * @param array $config The settings for this behavior.
 */
	public function __construct(Table $table, array $config = []) {
		$this->_defaultConfig = Hash::merge($this->_defaultConfig, (array)Configure::read('FileStorage.Behavior'));
		parent::__construct($table, $config);
		$this->_table = $table;
		if ($this->_config['validate'] === true) {
			$this->configureUploadValidation($this->_config['validator']);
		}
	}

/**
 * Configures upload related validation rules for a validator.
 *
 * @param string $validatorName Config name of a validator.
 * @param array $config Config options.
 * @return void
 */
	public function configureUploadValidation($validatorName = 'default', $config = []) {
		$uploadValidator = new \Burzum\FileStorage\Validation\UploadValidator();
		$validator = $this->_table->validator($validatorName);
		$validator->provider('UploadValidator', $uploadValidator);

		$config = $this->_config += $config;
		$this->removeUploadValidationRules($validatorName);

		if (!empty($config['validateImageSize'])) {
			$validator->add($config['fileField'], 'imageSize', [
				'provider' => 'UploadValidator',
				'rule' => [
					'imageSize',
					$config['validateImageSize']
				],
				'message' => __d('file_storage', 'The image dimensions are to big.')
			]);
		}
		if (is_int($config['validateFilesize'])) {
			$validator->add($config['fileField'], 'filesize', [
				'provider' => 'UploadValidator',
				'rule' => [
					'filesize',
					$config['validateFilesize']
				],
				'message' => __d('file_storage', 'The file is to big!')
			]);
		}
		if ($config['validateUploadErrors'] === true) {
			$validator->add($config['fileField'], 'uploadErrors', [
				'provider' => 'UploadValidator',
				'rule' => [
					'uploadErrors',
					['allowNoFileError' => $config['allowNoFileError']]
				],
				'message' => __d('file_storage', 'No file was uploaded.')
			]);
		}
		if ($config['validateUploadArray'] === true) {
			$validator->add($config['fileField'], 'uploadArray', [
				'provider' => 'UploadValidator',
				'rule' => [
					'isUploadArray'
				],
				'message' => __d('file_storage', 'Invalid upload!')
			]);
		}
		if (!empty($config['allowedMime'])) {
			$validator->add($config['fileField'], 'mimeType', [
				'provider' => 'UploadValidator',
				'rule' => [
					'mimeType',
					$config['allowedMime']
				],
				'message' => __d('file_storage', 'The mime-type is not allowed.')
			]);
		}
		if (!empty($config['allowedExtensions'])) {
			$validator->add($config['fileField'], 'extension', [
				'provider' => 'UploadValidator',
				'rule' => [
					'extension',
					$config['allowedExtensions']
				],
				'message' => __d('file_storage', 'The extension is not allowed.')
			]);
		};
		if ($config['localFile'] === true) {
			$validator->add($config['fileField'], 'localFile', [
				'provider' => 'UploadValidator',
				'rule' => [
					'isUploadedFile'
				],
				'message' => __d('file_storage', 'Invalid file.')
			]);
		}
	}

	public function removeUploadValidationRules($validatorName = 'default', $fieldName = null) {
		if (empty($fieldName)) {
			$fieldName = $this->_config['fileField'];
		}
		$validator = $this->_table->validator($validatorName);
		$rules = [
			'localFile',
			'extension',
			'mimeType',
			'uploadArray',
			'uploadErrors',
			'filesize',
			'imageSize'
		];
		foreach ($rules as $rule) {
			$validator->remove($fieldName, $rule);
		}
	}
}
