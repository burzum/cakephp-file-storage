<?php
namespace Burzum\FileStorage\Console\Command;

use Cake\ORM\TableRegistry;

/**
 * ImageShell
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class ImageVersionShell extends Shell {

/**
 * Models
 *
 * @var array
 */
	public $uses = array();

/**
 * Storage Table Object
 */
	public $Table = null;

/**
 * Limit
 *
 * @var integer
 */
	public $limit = 10;

/**
 * Program entry point
 *
 * @return void
 */
	public function main() {
		$storageModel = 'FileStorage.ImageStorage';
		if (isset($this->params['storageModel'])) {
			$storageModel = $this->params['storageModel'];
		}

		$this->Model = TableRegistry::get($storageModel);

		if (!$this->Model instanceOf \Burzum\FileStorage\Model\Table\ImageStorage) {
			$this->out(__d('file_storage', 'Invalid Storage Table: %s', $storageModel));
			$this->out(__d('file_storage', 'The table must be an instance of FileStorage\Model\Table\ImageStorage or extend it!'));
			$this->_stop();
		}

		if (isset($this->params['limit'])) {
			if (!is_numeric($this->params['limit'])) {
				$this->out(__d('file_storage', '--limit must be an integer!'));
				$this->_stop();
			}
			$this->limit = $this->params['limit'];
		}

		if ($this->command == 'generate' || $this->command == 'remove') {
			if (isset($this->args[1]) && isset($this->args[2])) {
				$operations = Configure::read('FileStorage.imageSizes.' . $this->args[1] . '.' . $this->args[2]);

				if (empty($operations)) {
					$this->out(__d('file_storage', 'Invalid table or version.'));
					$this->_stop();
				}

				try {
					$this->_loop($this->command, $this->args[1], array($this->args[2] => $operations));
				} catch (Exception $e) {
					$this->out($e->getMessage());
					$this->_stop();
				}

			} else {
				$this->out(__d('file_storage', 'Please use: generate <model> <version>'));
				$this->_stop();
			}
		}
	}

/**
 * getOptionParser
 *
 * @return Parser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->addSubcommand('generate', array(
			'help' => '<model> <version> Generate a new image version',
			'boolean' => true));
		$parser->addSubcommand('remove', array(
			'help' => '<model> <version> Remove an image version',
			'boolean' => true));
		$parser->addOption('storageModel', array(
			'short' => 's',
			'help' => __('The storage model for image processing you want to use.')));
		$parser->addOption('limit', array(
			'short' => 'l',
			'help' => __('Limits the amount of records to be processed in one batch')));
		return $parser;
	}

	protected function _loop($action, $model, $operations = array()) {
		if (!in_array($action, array('generate', 'remove'))) {
			$this->_stop();
		}

		$this->totaleImageCount = $this->Table->find('count', array(
			'recursive' => -1,
			'contain' => array(),
			'conditions' => array(
				$this->Table->alias() . '.model' => $model,
				$this->Table->alias() . '.extension' => array('jpg', 'png')
			)
		));

		if ($this->totaleImageCount > 0) {
			$this->out(__d('file_storage', '%d image files will be processed' . "\n", $this->totaleImageCount));

			$processed = 0;
			$options = array(
				'recursive' => -1,
				'contain' => array(),
				'conditions' => array(
					$this->Table->alias() . '.model' => $model,
					$this->Table->alias() . '.extension' => array('jpg', 'png')
				)
			);

			$offset = 0;
			$limit = $this->limit;

			do {
				$options['limit'] = $limit;
				$options['offset'] = $offset;
				$images = $this->Table->find('all', $options);

				if (!empty($images)) {
					foreach ($images as $image) {
						$Storage = StorageManager::adapter($image[$this->Table->alias()]['adapter']);
						if ($Storage === false) {
							$this->out(__d('file_storage', 'Cant load adapter config %s for record %s', $image[$this->Table->alias()]['adapter'], $image[$this->Table->alias()][$this->Table->primaryKey]));
						} else {
							$payload = array(
								'record' => $image,
								'storage' => $Storage,
								'operations' => $operations);

							if ($action == 'generate') {
								$Event = new Event('ImageVersion.createVersion', $this->Model, $payload);
								EventManager::instance()->dispatch($Event);
							}

							if ($action == 'remove') {
								$Event = new Event('ImageVersion.removeVersion', $this->Model, $payload);
								EventManager::instance()->dispatch($Event);
							}

							$this->out(__('%s processed', $image[$this->Table->alias()]['id']));
						}
					}
				}

				$offset += $limit;
			} while (!empty($images));
		} else {
			$this->out(__d('file_storage', 'No Images for model %s found', $model));
		}
	}

}