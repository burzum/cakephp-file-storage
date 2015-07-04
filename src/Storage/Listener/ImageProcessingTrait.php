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
 *
 */
	protected function _loadImageProcessingFromConfig() {
		$this->_imageVersions = Configure::read('FileStorage.imageSizes');
		$this->_imageVersionHashes = FileStorageUtils::generateHashes();
	}

/**
 *
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
 *
 */
	public function createImageVersions(Entity $entity) {
		if (!isset($this->_imageVersions[$entity->model])) {
			throw new \RuntimeException(sprintf('No image version config found for `%s`!', $entity->model));
		}
		$result = [];
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
				$this->imageProcessor()->open($entity->file['tmp_name']);
				$this->imageProcessor()->batchProcess($output, $config);
				$this->getAdapter($entity->adapter)->write($path, file_get_contents($output));
			} catch (\Exception $e) {
				$result[$version] = [
					'status' => 'error',
					'error' => $e->getMessage(),
				];
			}
		}
		return $result;
	}

/**
 *
 */
	public function getImageVersionHash($model, $version) {
		if (empty($this->_imageVersionHashes[$model][$version])) {
			throw new \RuntimeException(sprintf('Version "%s" for model "%s" does not exist!', $model, $version));
		}
		return $this->_imageVersionHashes[$model][$version];
	}

/**
 *
 */
	public function storeImageVersions($entity, array $versions) {

	}

/**
 *
 */
	public function removeImageVersions($entity, array $versions) {
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
