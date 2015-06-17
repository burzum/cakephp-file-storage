<?php
namespace Burzum\FileStorage\Storage;

use \Burzum\Imagine\Lib\ImageProcessor;

trait ImageProcessingTrait {

	public function imageProcessor(array $config = [], $renew = false) {
		if (!empty($this->_imageProcessor) && $renew === false) {
			return $this->_imageProcessor;
		}
		$this->_imageProcessor = new ImageProcessor($config);
		return $this->_imageProcessor;
	}

	public function createImageVersions() {

	}

	public function storeImageVersions() {

	}

	public function removeImageVersions() {

	}
}
