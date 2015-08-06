<?php
namespace Burzum\FileStorage\Storage\PathBuilder;

use Cake\Core\App;
use RuntimeException;

/**
 * @author Florian Krämer
 * @author Robert Pustułka
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class PathBuilderFactory {

/**
 * Builds the path builder for given name and options.
 *
 * @param string $name
 * @param array $options
 * @param bool $renewObject
 * @return \Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface
 */
	public static function create($name, array $options = []) {
		$className = App::className($name, 'Storage/PathBuilder', 'PathBuilder');
		if (!class_exists($className)) {
			$className = App::className('Burzum/FileStorage.' . $name, 'Storage/PathBuilder', 'PathBuilder');
		}
		if (!class_exists($className)) {
			throw new RuntimeException(sprintf('Could not find path builder "%s"!', $name));
		}
		$pathBuilder = new $className($options);
		if (!$pathBuilder instanceof PathBuilderInterface) {
			throw new RuntimeException(sprintf('Path builder class "%s" does not implement the PathBuilderInterface interface!', $name));
		}
		return $pathBuilder;
	}
}
