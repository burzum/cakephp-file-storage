<?php
namespace Burzum\FileStorage\View\Helper;

use Cake\Event\Event;
use Cake\Event\EventManager;

/**
 * LegacyImageHelper
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class LegacyImageHelper extends ImageHelper {

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

		$event = new Event('ImageVersion.getVersions', $this, $eventOptions);
		EventManager::instance()->dispatch($event);

		if ($event->isStopped()) {
			return $this->normalizePath($event->data['path']);
		}

		return false;
	}

}
