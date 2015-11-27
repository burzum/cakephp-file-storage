<?php
namespace Burzum\FileStorage\Model\Entity;

use Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait;
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
	use PathBuilderTrait;

	/**
	 * @param array $properties hash of properties to set in this entity
	 * @param array $options list of options to use when creating this entity
	 */
	public function __construct(array $properties = [], array $options = []) {
		$options += [
			'pathBuilder' => null,
			'pathBuilderOptions' => []
		];
		parent::__construct($properties, $options);

		if (is_string($options['pathBuilder'])) {
			$this->pathBuilder(
				$options['pathBuilder'],
				$options['pathBuilderOptions']
			);
		}
	}

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*' => true,
	];

	/**
	 * Accessor to get the *real* path on disk / backend + filename.
	 *
	 * @link http://book.cakephp.org/3.0/en/orm/entities.html#accessors-mutators
	 * @return string
	 */
	protected function _getFullPath() {
		$this->path();
	}

	/**
	 * Accessor to get the URL to this file.
	 *
	 * @link http://book.cakephp.org/3.0/en/orm/entities.html#accessors-mutators
	 * @return string
	 */
	protected function _getUrl() {
		$this->url();
	}

	/**
	 * Gets a path for this entities file.
	 *
	 * @param array $options Path options.
	 * @return string
	 */
	public function path(array $options = []) {
		if (empty($options['method'])) {
			$options['method'] = 'fullPath';
		}
		return $this->_path($options);
	}

	/**
	 * Gets an URL for this entities file.
	 *
	 * @param array $options Path options.
	 * @return string
	 */
	public function url(array $options = []) {
		$options['method'] = 'url';
		return $this->_path($options);
	}

	/**
	 * Gets a path for this entities file.
	 *
	 * @param array $options Path options.
	 * @return string
	 */
	protected function _path($options) {
		if (empty($options['method'])) {
			$options['method'] = 'path';
		}
		$event = $this->dispatchEvent('FileStorage.path', $options);
		return $event->result;
	}
}
