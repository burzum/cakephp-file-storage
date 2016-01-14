<?php
namespace Burzum\FileStorage\Model\Entity;

use Cake\Core\Configure;

/**
 * FileStorage Entity.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
class ImageStorage extends FileStorage {

	/**
	 * Gets the version of an image.
	 *
	 * @param string
	 * @param array $options
	 * @return string
	 */
	public function imageVersion($version, $options = []) {
		$options['version'] = $version;
		$options['image'] = $this;
		$options['hash'] = Configure::read('FileStorage.imageHashes.' . $this->_properties['model'] . '.' . $version);
		if (empty($options['hash'])) {
			throw new \InvalidArgumentException(sprintf('No valid version key (Identifier: `%s` Key: `%s`) passed!', $this->get('model'), $version));
		}
		$event = $this->dispatchEvent('ImageVersion.getVersions', $options);
		return $event->result;
	}
}
