<?php
namespace Burzum\FileStorage\View\Helper;

use Burzum\FileStorage\Storage\StorageUtils;
use Cake\Core\Configure;
use Cake\View\View;
use InvalidArgumentException;

/**
 * ImageHelper
 *
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class ImageHelper extends StorageHelper {

	/**
	 * Helpers
	 *
	 * @var array
	 */
	public $helpers = [
		'Html'
	];

	/**
	 * Constructor
	 *
	 * @param \Cake\View\View
	 * @param array $config
	 */
	public function __construct(View $view, array $config = []) {
		$this->_defaultConfig['pathPrefix'] = '';
		StorageUtils::generateHashes();

		parent::__construct($view, $config);
	}

	/**
	 * Generates an image url based on the image record data and the used Gaufrette adapter to store it
	 *
	 * @param array $image FileStorage array record or whatever else table that matches this helpers needs without the model, we just want the record fields
	 * @param string|null $version Image version string
	 * @param array $options HtmlHelper::image(), 2nd arg options array
	 * @return string
	 */
	public function display($image, $version = null, $options = []) {
		if (empty($image)) {
			return $this->fallbackImage($options, $image, $version);
		}

		$url = $this->imageUrl($image, $version, $options);
		if ($url !== false) {
			return $this->Html->image($url, $options);
		}

		return $this->fallbackImage($options, $image, $version);
	}

	/**
	 * Gets a hash.
	 *
	 * @param string $version
	 * @param array $image
	 * @return string|null
	 */
	protected function _getHash($version, $image) {
		if (!empty($version)) {
			$hash = Configure::read('FileStorage.imageHashes.' . $image['model'] . '.' . $version);
			if (empty($hash)) {
				throw new InvalidArgumentException(sprintf('No valid version key (Identifier: `%s` Key: `%s`) passed!', @$image['model'], $version));
			}

			return $hash;
		}

		return null;
	}

	/**
	 * URL
	 *
	 * @param array $image FileStorage array record or whatever else table that matches this helpers needs without the model, we just want the record fields
	 * @param string|null $version Image version string
	 * @param array $options HtmlHelper::image(), 2nd arg options array
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public function imageUrl($image, $version = null, $options = []) {
		$fileInfo = pathinfo($image['path']);
		$hash = $this->_getHash($version, $image);
		
		$version = $fileInfo['dirname'] . DS . $fileInfo['filename'];       
        if ($hash) {
            $version .= '.' . $hash;
        }

		if (!empty($fileInfo['extension'])) {
			$version .= '.' . $fileInfo['extension'];
		}

		if (!empty($options['pathPrefix'])) {
			return $this->normalizePath($options['pathPrefix'] . $version);
		}

		$pathPrefix = $this->getConfig('pathPrefix');
		if (!empty($pathPrefix)) {
			return $this->normalizePath($pathPrefix . $version);
		}

		return $this->normalizePath($version);
	}

	/**
	 * Provides a fallback image if the image record is empty
	 *
	 * @param array $options
	 * @param array $image
	 * @param string|null $version
	 * @return string
	 */
	public function fallbackImage($options = [], $image = [], $version = null) {
		if (isset($options['fallback'])) {
			if ($options['fallback'] === true) {
				$imageFile = 'placeholder/' . $version . '.jpg';
			} else {
				$imageFile = $options['fallback'];
			}
			unset($options['fallback']);

			return $this->Html->image($imageFile, $options);
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

}
