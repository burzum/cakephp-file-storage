<?php
namespace Burzum\FileStorage\Shell;

use Cake\Core\Configure;
use Cake\Console\Shell;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Burzum\FileStorage\Lib\StorageManager;
use Burzum\FileStorage\Model\Table\ImageStorageTable;

/**
 * ImageShell
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class ImageVersionShell extends Shell {

/**
 * Storage Table Object
 * @var \Cake\ORM\Table
 */
	public $Table = null;

/**
 * Limit
 *
 * @var integer
 */
	public $limit = 10;

/**
 * @inheritDoc
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->description([
			__d('file_storage', 'Shell command for generating and removing image versions.')
		]);
		$parser->addOption('storageTable', [
			'short' => 's',
			'help' => __d('file_storage', 'The storage table for image processing you want to use.')
		]);
		$parser->addOption('limit', [
			'short' => 'l',
			'help' => __d('file_storage', 'Limits the amount of records to be processed in one batch')
		]);
		$parser->addSubcommands([
			'generate' => [
				'help' => __d('file_storage', '<model> <version> Generate a new image version'),
				'parser' => [
					'arguments' => [
						'model' => [
							'help' => __d('file_storage', 'Value of the model property of the images to generate'),
							'required' => true,
						],
						'version' => [
							'help' => __d('file_storage', 'Image version to generate'),
							'required' => true,
						],
					],
					'options' => [
						'storageTable' => [
							'short' => 's',
							'help' => __d('file_storage', 'The storage table for image processing you want to use.'),
						],
						'limit' => [
							'short' => 'l',
							'help' => __d('file_storage', 'Limits the amount of records to be processed in one batch'),
						],
					],
				],
			],
			'remove' => [
				'help' => __d('file_storage', '<model> <version> Remove an new image version'),
				'parser' => [
					'arguments' => [
						'model' => [
							'help' => __d('file_storage', 'Value of the model property of the images to remove'),
							'required' => true,
						],
						'version' => [
							'help' => __d('file_storage', 'Image version to remove'),
							'required' => true,
						],
					],
					'options' => [
						'storageTable' => [
							'short' => 's',
							'help' => __d('file_storage', 'The storage table for image processing you want to use.'),
						],
						'limit' => [
							'short' => 'l',
							'help' => __d('file_storage', 'Limits the amount of records to be processed in one batch'),
						],
					],
				],
			],
			'regenerate' => [
				'help' => __d('file_storage', '<model> Generates all image versions.'),
				'parser' => [
					'arguments' => [
						'model' => [
							'help' => __d('file_storage', 'Value of the model property of the images to generate'),
							'required' => true,
						],
					],
					'options' => [
						'storageTable' => [
							'short' => 's',
							'help' => __d('file_storage', 'The storage table for image processing you want to use.'),
						],
						'limit' => [
							'short' => 'l',
							'help' => __d('file_storage', 'Limits the amount of records to be processed in one batch'),
						],
					],
				],
			],
		]);
		return $parser;
	}

/**
 * @inheritDoc
 */
	public function initialize() {
		parent::initialize();

		$storageTable = 'Burzum/FileStorage.ImageStorage';
		if (isset($this->params['storageTable'])) {
			$storageTable = $this->params['storageTable'];
		}

		$this->Table = TableRegistry::get($storageTable);

		if (!$this->Table instanceOf ImageStorageTable) {
			$this->out(__d('file_storage', 'Invalid Storage Table: {0}', $storageTable));
			$this->out(__d('file_storage', 'The table must be an instance of Burzum\FileStorage\Model\Table\ImageStorageTable or extend it!'));
			$this->_stop();
		}

		if (isset($this->params['limit'])) {
			if (!is_numeric($this->params['limit'])) {
				$this->out(__d('file_storage', '--limit must be an integer!'));
				$this->_stop();
			}
			$this->limit = $this->params['limit'];
		}
	}

/**
 * Generate all image versions.
 *
 * @param string $model
 */
	public function regenerate() {
		$operations = Configure::read('FileStorage.imageSizes.' . $this->args[0]);

		if (empty($operations)) {
			$this->out(__d('file_storage', 'Invalid table or version.'));
			$this->_stop();
		}

		foreach ($operations as $operation) {
			try {
				$this->_loop($this->command, $this->args[0], array($operation));
			} catch (\Exception $e) {
				$this->out($e->getMessage());
				$this->_stop();
			}
		}
	}

/**
 * Generate a given image version.
 *
 * @param string $model
 * @param string $version
 */
	public function generate($model, $version) {
		$operations = Configure::read('FileStorage.imageSizes.' . $model . '.' . $version);

		if (empty($operations)) {
			$this->out(__d('file_storage', 'Invalid table or version.'));
			$this->_stop();
		}

		try {
			$this->_loop('generate', $model, array($version => $operations));
		} catch (\Exception $e) {
			$this->out($e->getMessage());
			$this->_stop();
		}
	}

/**
 * Remove a given image version.
 *
 * @param string $model
 * @param string $version
 */
	public function remove($model, $version) {
		$operations = Configure::read('FileStorage.imageSizes.' . $model . '.' . $version);

		if (empty($operations)) {
			$this->out(__d('file_storage', 'Invalid table or version.'));
			$this->_stop();
		}

		try {
			$this->_loop('remove', $model, array($version => $operations));
		} catch (\Exception $e) {
			$this->out($e->getMessage());
			$this->_stop();
		}
	}

/**
 * Loops through image records and performs requested operation on them.
 *
 * @param $action
 * @param $model
 * @param array $operations
 */
	protected function _loop($action, $model, $operations = array()) {
		if (!in_array($action, array('generate', 'remove', 'regenerate'))) {
			$this->_stop();
		}

		$this->totalImageCount = $this->Table
			->find()
			->where(['model' => $model])
			->andWhere(['extension IN' => ['jpg', 'png']])
			->count();

		if ($this->totalImageCount == 0) {
			$this->out(__d('file_storage', 'No Images for model {0} found', $model));
			$this->_stop();
		}

		$this->out(__d('file_storage', '{0} image file(s) will be processed' . "\n", $this->totalImageCount));

		$offset = 0;
		$limit = $this->limit;

		do {
			$images = $this->Table
				->find()
				->where(['model' => $model])
				->andWhere(['extension IN' => ['jpg', 'png']])
				->limit($limit)
				->offset($offset)
				->all();

			if (!empty($images)) {
				foreach ($images as $image) {
					$Storage = StorageManager::adapter($image->adapter);
					if ($Storage === false) {
						$this->out(__d('file_storage', 'Cant load adapter config {0} for record {1}', $image->adapter, $image->id));
					} else {
						$payload = array(
							'record' => $image,
							'storage' => $Storage,
							'operations' => $operations);

						if ($action == 'generate' || $action == 'regenerate') {
							$Event = new Event('ImageVersion.createVersion', $this->Table, $payload);
							EventManager::instance()->dispatch($Event);
						}

						if ($action == 'remove') {
							$Event = new Event('ImageVersion.removeVersion', $this->Table, $payload);
							EventManager::instance()->dispatch($Event);
						}

						$this->out(__('{0} processed', $image->id));
					}
				}
			}
			$offset += $limit;
		} while ($images->count() > 0);
	}
}
