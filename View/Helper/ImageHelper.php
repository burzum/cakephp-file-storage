<?php
/**
 * ImageHelper
 *
 * @author Florian Krämer
 * @copyright 2012 Florian Krämer
 * @license MIT
 */
class ImageHelper extends AppHelper {

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array('Html');

/**
 * Generates an image url based on the image record data and the used gaufrette adapter to store it
 *
 * @param array $image FileStorage array record or whatever else table that matches this helpers needs without the model, we just want the record fields
 * @param string $version Image version string
 * @param array $options HtmlHelper::image(), 2nd arg options array
 * @return string
 */
	public function image($image, $version = null, $options = array()) {
		if (empty($image) || empty($image['id'])) {
			return $this->fallbackImage($options);
		}

		$hash = Configure::read('Media.imageHashes.' . $image['model'] . '.' . $version);
		if (empty($hash)) {
			throw new InvalidArgumentException(__('No valid version key passed!'));
		}

		$method = '_' . Inflector::camelize($image['adapter']) . 'Adapter';
		$path = $this->{$method}($image, $version, $hash);

		if ($path === false) {
			return $this->fallbackImage($options);
		}

		return $this->Html->image('/' . $path, $options);
	}

/**
 * Provides a fallback image if the image record is empty
 *
 * @param array $options
 * @return string
 */
	public function fallbackImage($options = array()) {
		if (isset($options['fallback'])) {
			$image = $options['fallback'];
			unset($options['fallback']);
			return $this->Html->image($image, $options);
		}
		return '';
	}

/**
 * Turns the windows \ into / so that the path can be used in an url
 *
 * @param string $path
 * @return string
 */
	public function normalizePath($path) {
		return str_replace('\\', '/', $path);
	}

/**
 * Processes an image record and builts that path that was created by gaufrettes local adapter
 *
 * @param array $image
 * @param string $version
 * @param string $hash
 */
	protected function _localAdapter($image, $version = null, $hash) {
		$path = $this->normalizePath($image['path']);
		$path = $path . str_replace('-', '', $image['id']);
		$path .= '.' . $hash . '.' . $image['extension'];
		return $path;
	}

/**
 * @todo 
 * @param array $image
 * @param string $version
 * @param string $hash
 */
	protected function _amazonS3Adapter($image, $version = null, $hash) {
		
	}

}