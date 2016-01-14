<?php
namespace Burzum\FileStorage\View\Helper;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * ImageHelper
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
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
	public $helpers = array(
		'Html'
	);

	/**
	 * Generates an image url based on the image record data and the used Gaufrette adapter to store it
	 *
	 * @param array $image FileStorage array record or whatever else table that matches this helpers needs without the model, we just want the record fields
	 * @param string $version Image version string
	 * @param array $options HtmlHelper::image(), 2nd arg options array
	 * @return string
	 */
	public function display($image, $version = null, $options = array()) {
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
				throw new \InvalidArgumentException(sprintf('No valid version key (Identifier: `%s` Key: `%s`) passed!', @$image['model'], $version));
			}
			return $hash;
		}
		return null;
	}

	/**
	 * URL
	 *
	 * @param array $image FileStorage array record or whatever else table that matches this helpers needs without the model, we just want the record fields
	 * @param string $version Image version string
	 * @param array $options HtmlHelper::image(), 2nd arg options array
	 * @throws InvalidArgumentException
	 * @return string
	 */
	public function imageUrl($image, $version = null, $options = []) {
		if (empty($image) || empty($image['id'])) {
			return false;
		}

		$eventOptions = [
			'hash' => $this->_getHash($version, $image),
			'image' => $image,
			'version' => $version,
			'options' => $options,
			'pathType' => 'url'
		];

		$event1 = new Event('ImageVersion.getVersions', $this, $eventOptions);
		$event2 = new Event('FileStorage.ImageHelper.imagePath', $this, $eventOptions);

		EventManager::instance()->dispatch($event1);
		EventManager::instance()->dispatch($event2);

		if ($event1->isStopped()) {
			return $this->normalizePath($event1->data['path']);
		} elseif ($event2->isStopped()) {
			return $this->normalizePath($event2->data['path']);
		}
		return false;
	}

	/**
	 * Provides a fallback image if the image record is empty
	 *
	 * @param array $options
	 * @param array $image
	 * @param string $version
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
