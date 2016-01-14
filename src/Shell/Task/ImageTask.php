<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Shell\Task;

use Burzum\FileStorage\Storage\StorageTrait;
use Burzum\FileStorage\Storage\StorageException;
use Cake\Console\Shell;
use Cake\Event\EventManagerTrait;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;

/**
 * Task to generate and remove image versions based on the identifier and the versions.
 *
 * The identifier is the "model" field from the storage table. Versions is a comma
 * separated list of image versions configured for the given identifier.
 *
 * bin\cake burzum/FileStorage.storage image remove <identifier> <versions>
 * bin\cake burzum/FileStorage.storage image remove ProfilePicture "thumb60, crop50"
 */
class ImageTask extends Shell {

	use StorageTrait;
	use EventManagerTrait;

	public function initialize() {
		$this->Table = TableRegistry::get('Burzum/FileStorage.ImageStorage');
	}

/**
 * Remove image versions.
 *
 * @return void
 */
	public function remove() {
		$this->_loop($this->args[0], explode(',', $this->args[1]), 'remove');
	}

/**
 * Create image versions.
 *
 * @return void
 */
	public function generate() {
		$this->_loop($this->args[0], explode(',', $this->args[1]), 'generate');
	}

/**
 * Loops through image records and performs requested operations on them.
 *
 * @param string $identifier
 * @return void
 */
	protected function _loop($identifier, $options, $action) {
		$count = $this->_getCount($identifier);
		$offset = 0;
		$limit = $this->params['limit'];

		$this->out(__d('file_storage', '{0} record(s) will be processed.' . "\n", $count));

		do {
			$records = $this->_getRecords($identifier, $limit, $offset);
			if (!empty($records)) {
				foreach ($records as $record) {
					$method = '_' . $action . 'Image';
					try {
						$this->{$method}($record, $options);
					} catch (StorageException $e) {
						$this->err($e->getMessage());
					}
				}
			}
			$offset += $limit;
			$this->out(__d('file_storage', '{0} of {1} records processed.', [$limit, $count]));
		} while ($records->count() > 0);
	}

/**
 * Triggers the event to remove image versions.
 *
 * @param \Cake\ORM\Entity
 * @param array
 * @return void
 */
	protected function _removeImage($record, $options) {
		$Event = new Event('ImageVersion.removeVersion', $this->Table, [
			'record' => $record,
			'operations' => $options
		]);
		EventManager::instance()->dispatch($Event);
	}

/**
 * Triggers the event to generate the new images.
 *
 * @param \Cake\ORM\Entity
 * @param array
 * @return void
 */
	protected function _generateImage($record, $options) {
		$Event = new Event('ImageVersion.createVersion', $this->Table, [
			'record' => $record,
			'operations' => $options
		]);
		EventManager::instance()->dispatch($Event);
	}

/**
 * Gets the records for the loop.
 *
 * @param string $identifier
 * @param integer $limit
 * @param integer $offset
 * @return \Cake\ORM\ResultSet
 */
	public function _getRecords($identifier, $limit, $offset) {
		return $this->Table
			->find()
			->where([$this->Table->alias() . '.model' => $identifier])
			->limit($limit)
			->offset($offset)
			->all();
	}

/**
 * Gets the amount of records for an identifier in the DB.
 *
 * @param string $identifier
 * @return integer
 */
	protected function _getCount($identifier) {
		$count = $this->_getCountQuery($identifier)->count();
		if ($count === 0) {
			$this->out(__d('file_storage', 'No records for identifier "{0}" found.', $identifier));
			$this->_stop();
		}
		return $count;
	}

/**
 * Gets the query object for the count.
 *
 * @param string $identifier
 * @return \Cake\ORM\Query
 */
	protected function _getCountQuery($identifier) {
		return $this->Table
			->find()
			->where([$this->Table->alias() . '.model' => $identifier]);
	}

	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->addOption('model', [
			'short' => 'm',
			'help' => __('The model to use.'),
			'default' => 'Burzum/FileStorage.ImageStorage'
		]);
		$parser->addOption('limit', [
			'short' => 'l',
			'help' => __('The limit of records to process in a batch.'),
			'default' => 50
		]);
		$parser->addOption('versions', [
			'short' => 's',
			'help' => __('The model to use.'),
			'default' => 'Burzum/FileStorage.ImageStorage'
		])
		->addSubcommand('remove', [
			'remove' => 'Remove image versions.'
		])
		->addSubcommand('generate', [
			'remove' => 'Generate image versions.'
		]);
		$parser->addArguments([
			'identifier' => ['help' => 'The identifier to process', 'required' => true],
			'versions' => ['help' => 'The identifier to process', 'required' => true],
		]);
		return $parser;
	}
}
