<?php

namespace Burzum\FileStorage\Storage\Image;

use Burzum\FileStorage\Storage\PathBuilder\PathBuilderFactory;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use InvalidArgumentException;

/**
 * VersionUtils
 *
 * @author Robert Pustułka
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class VersionUtils {

	protected static $_pathBuilders = [];

	/**
	 * Returns full path for image version.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity Image entity.
	 * @param string $version Version key.
	 * @param array $options Additional options.
	 * @return string
	 */
	public static function path(EntityInterface $entity, $version, $options = []) {
		$hash = static::_getHash($entity, $version);
		$pathBuilder = static::_getPathBuilder($entity->get('adapter'));

		$options += [
			'fileSuffix' => $hash ? ('.' . $hash) : ''
		];

		return $pathBuilder->fullPath($entity, $options);
	}

	/**
	 * Returns url for image version.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity Image entity.
	 * @param string $version Version key.
	 * @param array $options Additional options.
	 * @return string
	 */
	public static function url(EntityInterface $entity, $version, $options = []) {
		$hash = static::_getHash($entity, $version);
		$pathBuilder = static::_getPathBuilder($entity->get('adapter'));

		$options += [
			'fileSuffix' => $hash ? ('.' . $hash) : ''
		];

		return $pathBuilder->url($entity, $options);
	}

	/**
	 * Returns hash for a version or no hash for 'original' version key.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity Image entity.
	 * @param string $version Version key.
	 * @return string
	 */
	protected static function _getHash($entity, $version) {
		$hash = Configure::read('FileStorage.imageHashes.' . $entity->get('model') . '.' . $version);

		if ($hash === null) {
			if ($version === 'original') {
				return '';
			}

			$msg = sprintf('No valid version key (Identifier: `%s` Key: `%s`) passed!', $entity->get('model'), $version);
			throw new InvalidArgumentException($msg);
		}

		return $hash;
	}

	/**
	 * Constructs and caches path builder instances.
	 *
	 * @param string $name Path builder name.
	 * @return \Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface
	 */
	protected static function _getPathBuilder($name) {
		if (!isset(static::$_pathBuilders[$name])) {
			static::$_pathBuilders[$name] = PathBuilderFactory::create($name, ['modelFolder' => true]);
		}

		return static::$_pathBuilders[$name];
	}
}
