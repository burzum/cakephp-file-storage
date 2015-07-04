<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

use \Cake\Core\Configure;
use \Cake\ORM\Entity;
use \Burzum\Imagine\Lib\ImageProcessor;
use \Burzum\FileStorage\Lib\FileStorageUtils;

/**
 * ImageProcessingTrait
 *
 * Use this trait for Storage Listeners  that should process and upload image files.
 */
trait ImageProcessingTrait {

	protected $_imageProcessor = null;
	protected $_imageVersions = [];
	protected $_imageVersionHashes = [];

/**
 * Loads the image processing configuration into the class.
 */
	protected function _loadImageProcessingFromConfig() {
		$this->_imageVersions = (array)Configure::read('FileStorage.imageSizes');
		$this->_imageVersionHashes = FileStorageUtils::generateHashes();
	}

/**
 * Gets the image processor instance.
 *
 * @param array $config
 * @return mixed
 */
	public function imageProcessor(array $config = [], $renew = false) {
		if (!empty($this->_imageProcessor) && $renew === false) {
			return $this->_imageProcessor;
		}
		$this->_loadImageProcessingFromConfig();
		$this->_imageProcessor = new ImageProcessor($config);
		return $this->_imageProcessor;
	}

/**
 * Gets the hash of a specific image version for an entity.
 *
 * @param string $model Model identifier.
 * @param string $version Version identifier.
 * @return string
 */
	public function getImageVersionHash($model, $version) {
		if (empty($this->_imageVersionHashes[$model][$version])) {
			throw new \RuntimeException(sprintf('Version "%s" for model "%s" does not exist!', $model, $version));
		}
		return $this->_imageVersionHashes[$model][$version];
	}

/**
 * Creates the image versions of an entity.
 *
 * @todo finish me
 * @param \Cake\ORM\Entity $entity
 * @return array
 */
	public function createImageVersions(Entity $entity) {
		if (!isset($this->_imageVersions[$entity->model])) {
			throw new \RuntimeException(sprintf('No image version config found for `%s`!', $entity->model));
		}
		$result = [];
		$storage = $this->getAdapter($entity->adapter);
		foreach ($this->_imageVersions[$entity->model] as $version => $config) {
			$output = $this->createTmpFile();
			$hash = $this->getImageVersionHash($entity->model, $version);
			$path = $this->pathBuilder()->fullPath($entity, ['fileSuffix' => '.' . $hash]);
			$result[$version] = [
				'status' => 'success',
				'path' => $path,
				'hash' => $this->_imageVersionHashes[$entity->model][$version],
			];
			try {
				$tmpFile = $this->_tmpFile($storage,  $this->pathBuilder()->fullPath($entity));
				$this->imageProcessor()->open($tmpFile);
				$this->imageProcessor()->batchProcess($output, $config, ['format' => $entity->extension]);
				$storage->write($path, file_get_contents($output));
				unlink($tmpFile);
			} catch (\Exception $e) {
				$result[$version] = [
					'status' => 'error',
					'error' => $e->getMessage(),
					'line' => $e->getLine(),
					'file' => $e->getFile()
				];
			}
		}
		return $result;
	}

/**
 * Gets all image version config keys for a specific identifier.
 *
 * @param string $identifier
 * @throws \RuntimeException
 * @return array
 */
	public function getAllVersionsKeysForModel($identifier) {
		if (!isset($this->_imageVersions[$identifier])) {
			throw new \RuntimeException(sprintf('No image config present for identifier "%s"!', $identifier));
		}
		return array_keys($this->_imageVersions[$identifier]);
	}

/**
 * Convenience method to delete ALL versions for an entity.
 *
 * @param \Cake\ORM\Entity
 * @return array
 */
	public function removeAllImageVersions(Entity $entity) {
		return $this->removeAllImageVersions($entity, $this->getAllVersionsKeysForModel($entity->model));
	}

/**
 * Removes image versions of an entity.
 *
 * @param \Cake\ORM\Entity $entity
 * @param array List of image version to remove for that entity.
 * @return array
 */
	public function removeImageVersions(Entity $entity, array $versions) {
		$result = [];
		foreach ($versions as $version) {
			$hash = $this->getImageVersionHash($entity->model, $version);
			$path = $this->pathBuilder()->fullPath($entity, ['fileSuffix' => '.' . $hash]);
			$result[$version] = [
				'status' => 'success',
				'hash' => $hash,
				'path' => $path
			];
			try {
				$this->getAdapter($entity->adapter)->delete($path);
			} catch (\Exception $e) {
				$result[$version]['status'] = 'error';
				$result[$version]['error'] = $e->getMessage();
			}
		}
		return $result;
	}
}
