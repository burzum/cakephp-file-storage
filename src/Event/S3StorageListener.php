<?php
namespace Burzum\FileStorage\Event;

use Cake\Event\Event;

/**
 * S3StorageListener
 *
 * @author Florian Krämer
 * @copy 2013 - 2015 Florian Krämer
 * @license MIT
 */
class S3StorageListener extends AbstractStorageEventListener {

/**
 * Adapter classes this listener can work with
 *
 * @var array
 */
	protected $_adapterClasses = array(
		'\Gaufrette\Adapter\AmazonS3',
		'\Gaufrette\Adapter\AwsS3',
	);

/**
 * Implemented Events
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'FileStorage.afterSave' => 'afterSave',
			'FileStorage.afterDelete' => 'afterDelete',
		);
	}

/**
 * afterDelete
 *
 * @param \Cake\Event\Event $event
 * @return boolean|null
 */
	public function afterDelete(Event $event) {
		if ($this->_checkEvent($event)) {
			$table = $event->subject();
			$record = $event->data['record'][$table->alias()];
			$path = $this->_buildPath($event);
			try {
				$Storage = $this->getAdapter($record['adapter']);
				if (!$Storage->has($path['combined'])) {
					return false;
				}
				$Storage->delete($path['combined']);
			} catch (\Exception $e) {
				$this->log($e->getMessage());
				return false;
			}
			return true;
		}
	}

/**
 * afterSave
 *
 * @param Event $event
 * @return void
 */
	public function afterSave(Event $event) {
		if ($this->_checkEvent($event)) {
			$table = $event->subject();
			$record = $event->data['record'];
			$Storage = $this->getAdapter($record['adapter']);

			try {
				$path = $this->buildPath($event->subject(), $event->data['record']);
				$record['path'] = $path['path'];
				$Storage->write($path['combined'], file_get_contents($record['file']['tmp_name']), true);
				$table->save($record, array(
					'validate' => false,
					'callbacks' => false)
				);
			} catch (\Exception $e) {
				$this->log($e->getMessage());
			}
		}
	}

/**
 * Builds the storage path for this adapter.
 *
 * @param \Cake\ORM\Table $table
 * @param \Cake\Datasource\EntityInterface $entity
 * @return array
 */
	public function buildPath($table, $entity) {
		$adapterConfig = $this->getAdapterconfig($entity['adapter']);
		$id = $entity[$table->primaryKey()];

		$path = $this->fsPath('files' . DS . $entity['model'], $id);
		$path = '/' . str_replace('\\', '/', $path);

		if ($this->_config['preserveFilename'] === false) {
			$filename = $this->stripDashes($id);
			if ($this->_config['preserveExtension'] === true && !empty($entity['extension'])) {
				$filename .= '.' . $entity['extension'];
			}
		} else {
			$filename = $entity['filename'];
		}

		$combined = $path . $filename;
		$url = 'https://' . $adapterConfig['adapterOptions'][1] . '.s3.amazonaws.com' . $combined;

		return [
			'filename' => $filename,
			'path' => $path,
			'combined' => $path . $filename,
			'url' => $url
		];
	}
}
