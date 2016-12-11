<?php
namespace Burzum\FileStorage\Model\Behavior;

use Burzum\FileStorage\Storage\StorageUtils;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Behavior;
use Cake\ORM\TableRegistry;

/**
 * Upload Behavior
 *
 * Please note that this behavior is the most convenient but not the most
 * powerful way of handling file uploads!
 *
 * Options:
 *
 * - `defaults`: contains the default settings applied to each file upload.
 * - `files`: String or array list of files with configuration options for each file.
 * - `uploadOn`: Callback when the files should be processed, `afterSave` by default.
 */
class UploadBehavior extends Behavior {

	/**
	 * Default config
	 *
	 * @var array
	 */
	protected $_defaultConfig = [
		'defaults' => [
			'adapterConfig' => 'Local',
			'model' => 'Burzum/FileStorage.FileStorage',
			'association' => null,
		],
		'files' => 'file',
		'uploadOn' => 'afterSave'
	];

	/**
	 * After save callback.
	 *
	 * @param \Cake\Event\Event $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @return void
	 */
	public function afterSave(Event $event, EntityInterface $entity) {
		if ($this->config('uploadOn') === 'afterSave') {
			$this->_handleFiles($entity);
		}
	}

	/**
	 * Before save callback.
	 *
	 * @param \Cake\Event\Event $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @return void
	 */
	public function beforeSave(Event $event, EntityInterface $entity) {
		if ($this->config('uploadOn') === 'beforeSave') {
			$this->_handleFiles($entity);
		}
	}

	/**
	 * This method is looking for the actual file upload fields and processes them.
	 *
	 * @param \Cake\Datasource\EntityInterface
	 * @return array
	 */
	protected function _handleFiles(EntityInterface $entity) {
		$files = $this->config('file');
		if (is_string($files)) {
			$files = [$files => $this->config('defaults')];
		}

		$results = [];
		foreach ($files as $key => $file) {
			if (is_string($key)) {
				$field = $key;
				$options = $this->config('defaults');
				$options += $file;
			}
			if (is_string($file)) {
				$field = $file;
				$options = $this->config('defaults');
			}
			$results[$field] = $this->saveFile($entity->{$field}, $options);
		}

		return $results;
	}

	/**
	 * Gets the storage table instance.
	 *
	 * @param array $options Options.
	 * @return \Cake\ORM\Table
	 */
	protected function _getStorageModel($options) {
		if (!empty($options['association'])) {
			return $this->{$options['association']};
		}

		return TableRegistry::get($options['model']);
	}

	/**
	 * @param array|string $file
	 * @param \Cake\ORM\Table $table
	 * @param array $options
	 * @return \Cake\Datasource\EntityInterface
	 */
	protected function _composeEntity($file, $table, $options) {
		if (isset($options['validate']) && is_callable($options['validate'])) {
			$validator = $table->validationDefault();
			$validator = $options['validate']($validator);
			$table->validator('_fileUploadValidator', $validator);
		}

		$entity = $table->newEntity([
			'file' => $file,
			'adapter' => $options['adapterConfig']
		]);

		if (!empty($options['data'])) {
			$entity = $table->patchEntity($entity, $options['data']);
		}

		return $entity;
	}

	/**
	 * Save a file.
	 *
	 * @param array|string $file
	 * @param array $options
	 */
	public function saveFile($file, $options = []) {
		$defaults = $this->config('defaults');
		$defaults += $options;
		$options = $defaults;

		if (is_string($file)) {
			$file = StorageUtils::fileToUploadArray($file);
		}

		$model = $this->_getStorageModel($options);
		$entity = $this->_composeEntity($file, $model, $options);

		$model->save($entity);
		return $entity;
	}
}
