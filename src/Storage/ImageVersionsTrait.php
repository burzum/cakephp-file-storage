<?php
namespace Burzum\FileStorage\Storage;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventManager;
use Cake\Event\Event;

trait ImageVersionsTrait {

	/**
	 * Gets a list of image versions for a given record.
	 * Use this method to get a list of ALL versions when needed or to cache all the
	 * versions somewhere. This method will return all configured versions for an
	 * image. For example you could store them serialized along with the file data
	 * by adding a "versions" field to the DB table and extend this model.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity An ImageStorage database record
	 * @param array $options Options for the version.
	 * @return array A list of versions for this image file. Key is the version, value is the path or URL to that image.
	 */
	public function getImageVersions(EntityInterface $entity, $options = []) {
		$versionData = $this->_getImageVersionData($entity, $options);
		$versions = [];
		foreach ($versionData as $version => $data) {
			$hash = Configure::read('FileStorage.imageHashes.' . $entity->get('model') . '.' . $version);
			$eventData = [
				'hash' => $hash,
				'image' => $entity,
				'version' => $version,
				'options' => []
			];
			if (method_exists($this, 'dispatchEvent')) {
				$event = $this->dispatchEvent('ImageVersion.getVersions', $eventData);
			} else {
				$event = new Event('ImageVersion.getVersions', $eventData);
				EventManager::instance()->dispatch($event);
			}
			if ($event->isStopped()) {
				$versions[$version] = str_replace('\\', '/', $event->data['path']);
			}
		}
		return $versions;
	}

	/**
	 * Gets the image version data used to generate the versions.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity An ImageStorage database record.
	 * @param array $options Options for the version.
	 * @return array Version data information as array.
	 */
	protected function _getImageVersionData(EntityInterface $entity, array $options = []) {
		$versionData = (array)Configure::read('FileStorage.imageSizes.' . $entity->get('model'));
		if (isset($options['originalVersion'])) {
			$versionData['original'] = $options['originalVersion'];
		} else {
			Configure::write('FileStorage.imageSizes.' . $entity->get('model') . '.original', []);
			$versionData['original'] = [];
		}
		$versionData['original'] = isset($options['originalVersion']) ? $options['originalVersion'] : 'original';
		return $versionData;
	}
}
