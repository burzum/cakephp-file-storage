<?php
namespace Burzum\FileStorage\Model\Entity;

use Cake\Event\EventDispatcherTrait;
use Cake\ORM\Entity;

/**
 * FileStorage Entity.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class FileStorage extends Entity {

	use EventDispatcherTrait;

/**
 * Fields that can be mass assigned using newEntity() or patchEntity().
 *
 * @var array
 */
	protected $_accessible = [
		'*' => true,
	];

/**
 * Gets a path for this entities file.
 *
 * @param array $options
 * @return string
 */
	public function path(array $options = []) {
		$options['method'] = 'fullPath';
		return $this->_path($options);
	}

/**
 * Gets an URL for this entities file.
 *
 * @param array $options
 * @return string
 */
	public function url(array $options = []) {
		$options['method'] = 'url';
		return $this->_path($options);
	}

/**
 * Gets a path for this entities file.
 *
 * @param array $options
 * @return string
 */
	protected function _path($options) {
		$event = $this->dispatchEvent('FileStorage.path', $options);
		return $event->result;
	}
}
