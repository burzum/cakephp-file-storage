<?php
namespace Burzum\FileStorage\Storage\Listener;

use \Cake\Core\Configure;
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

	protected function _loadImageProcessingFromConfig() {
		$this->_imageVersions = Configure::read('FileStorage.imageSizes');
		$this->_imageVersionHashes = FileStorageUtils::generateHashes();
	}

	public function imageProcessor(array $config = [], $renew = false) {
		if (!empty($this->_imageProcessor) && $renew === false) {
			return $this->_imageProcessor;
		}
		$this->_loadImageProcessingFromConfig();
		$this->_imageProcessor = new ImageProcessor($config);
		return $this->_imageProcessor;
	}

	public function createImageVersions($entity, array $options = []) {
		$result = [];
		if (!isset($this->_imageVersions[$entity->model])) {
			throw new \RuntimeException(sprintf('No image version config found for `%s`!', $entity->model));
		}
		foreach ($this->_imageVersions[$entity->model] as $version => $config) {
			$output = $this->createTmpFile();
			try {
				$this->imageProcessor()->open($entity->file['tmp_name']);
				$this->imageProcessor()->batchProcess($output, $config);
				$result[$version] = [
					'status' => 'success',
					'imageFile' => $output,
					'hash' => $this->_imageVersionHashes[$entity->model][$version],
				];
			} catch (\Exception $e) {
				$result[$version] = [
					'status' => 'error',
					'error' => $e->getMessage(),
				];
			}
		}
		//debug($this->_imageVersions);
		//debug($this->_imageVersionHashes);
		return $result;
	}

	public function storeImageVersions($entity, array $options = []) {

	}

	public function removeImageVersions($entity, array $options = []) {

	}

	public function imageVersionFilename() {

	}
}
