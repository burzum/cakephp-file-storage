<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\PathBuilder;

use Cake\Core\App;
use RuntimeException;

trait PathBuilderTrait {

/**
 * Local PathBuilderInterface instance.
 *
 * @var \Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface
 */
	protected $_pathBuilder;

/**
 * Builds the path builder for given interface.
 *
 * @param string $name
 * @param array $options
 * @param bool $renewObject
 * @return \Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface
 */
	public function createPathBuilder($name, array $options = []) {
		$className = App::className($name, 'Storage/PathBuilder', 'PathBuilder');
		if (!class_exists($className)) {
			$className = App::className('Burzum/FileStorage.' . $name, 'Storage/PathBuilder', 'PathBuilder');
		}
		if (!class_exists($className)) {
			throw new RuntimeException(sprintf('Could not find path builder "%s"!', $className));
		}
		$pathBuilder = new $className($options);
		if (!$pathBuilder instanceof PathBuilderInterface) {
			throw new RuntimeException(sprintf('Path builder class "%s" does not implement the PathBuilderInterface interface!', $className));
		}
		return $pathBuilder;
	}

/**
 * Accessor/mutator for local PathBuilderInterface instance.
 *
 * @param \Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface $pathBuilder
 * @return \Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface
 */
	public function pathBuilder(PathBuilderInterface $pathBuilder = null) {
		if ($pathBuilder !== null) {
			$this->_pathBuilder = $pathBuilder;
		}
		return $this->_pathBuilder;
	}
}
