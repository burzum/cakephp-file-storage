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
use \Burzum\FileStorage\Storage\StorageUtils;

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
 * Convenience method to auto create ALL and auto remove ALL image versions for
 * an entity.
 *
 * Call this in your listener after you stored or removed a file that has image
 * versions. If you need more details in your logic around creating or removing
 * image versions use the other methods from this trait to implement the checks
 * and behavior you need.
 *
 * @param \Cake\ORM\Entity
 * @param string $action `create` or `remove`
 * @return array
 */
	public function autoProcessImageVersions(Entity $entity, $action) {
		if (!in_array($action, ['create', 'remove'])) {
			throw new \InvalidArgumentException();
		}
		$this->_loadImageProcessingFromConfig();
		if (!isset($this->_imageVersions[$entity->model])) {
			return false;
		}
		$method = $action . 'AllImageVersions';
		return $this->{$method}($entity);
	}

/**
 * Loads the image processing configuration into the class.
 *
 * @return void
 */
	protected function _loadImageProcessingFromConfig() {
		$this->_imageVersions = (array)Configure::read('FileStorage.imageSizes');
		$this->_imageVersionHashes = StorageUtils::generateHashes();
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
			throw new \RuntimeException(sprintf('Version "%s" for identifier "%s" does not exist!', $model, $version));
		}
		return $this->_imageVersionHashes[$model][$version];
	}

/**
 * Check that the image versions exist before doing something with them.
 *
 * @throws \RuntimeException
 * @param string $identifier
 * @param array $versions
 * @return void
 */
	protected function _checkImageVersions($identifier, array $versions) {
		if (!isset($this->_imageVersions[$identifier])) {
			throw new \RuntimeException(sprintf('No image version config found for identifier "%s"!', $identifier));
		}
		foreach ($versions as $version) {
			if (!isset($this->_imageVersions[$identifier][$version])) {
				throw new \RuntimeException(sprintf('Invalid version "%s" for identifier "%s"!', $identifier, $version));
			}
		}
	}

/**
 * Creates the image versions of an entity.
 *
 * @param \Cake\ORM\Entity $entity
 * @param array $versions $options
 * @param array $options
 * @return array
 */
	public function createImageVersions(Entity $entity, array $versions, array $options = []) {
		$this->_checkImageVersions($entity->model, $versions);

		$result = [];
		$storage = $this->storageAdapter($entity->adapter);
		foreach ($this->_imageVersions[$entity->model] as $version => $config) {
			if (!in_array($version, $versions)) {
				continue;
			}
			$hash = $this->getImageVersionHash($entity->model, $version);
			$path = $this->pathBuilder()->fullPath($entity, ['fileSuffix' => '.' . $hash]);
			$result[$version] = [
				'status' => 'success',
				'path' => $path,
				'hash' => $this->_imageVersionHashes[$entity->model][$version],
			];
			try {
				$output = $this->createTmpFile();
				$tmpFile = $this->_tmpFile($storage, $this->pathBuilder()->fullPath($entity));
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
 * Removes image versions of an entity.
 *
 * @param \Cake\ORM\Entity $entity
 * @param array List of image version to remove for that entity.
 * @param array $versions
 * @param array $options
 * @return array
 */
	public function removeImageVersions(Entity $entity, array $versions, array $options = []) {
		$this->_checkImageVersions($entity->model, $versions);

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
				$this->storageAdapter($entity->adapter)->delete($path);
			} catch (\Exception $e) {
				$result[$version]['status'] = 'error';
				$result[$version]['error'] = $e->getMessage();
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
 * Convenience method to create ALL versions for an entity.
 *
 * @param \Cake\ORM\Entity
 * @return array
 */
	public function createAllImageVersions(Entity $entity, array $options = []) {
		return $this->createImageVersions(
			$entity,
			$this->getAllVersionsKeysForModel($entity->model),
			$options
		);
	}

/**
 * Convenience method to delete ALL versions for an entity.
 *
 * @param \Cake\ORM\Entity
 * @return array
 */
	public function removeAllImageVersions(Entity $entity, array $options = []) {
		return $this->removeImageVersions(
			$entity,
			$this->getAllVersionsKeysForModel($entity->model),
			$options
		);
	}
}
