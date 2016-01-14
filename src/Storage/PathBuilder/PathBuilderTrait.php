<?php
/**
 * @author Florian Krämer
 * @author Robert Pustułka
 * @copyright 2012 - 2016 Florian Krämer
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
	protected $_pathBuilder = null;

	/**
	 * Builds the path builder for given interface.
	 *
	 * @param string $name
	 * @param array $options
	 * @return \Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface
	 */
	public function createPathBuilder($name, array $options = []) {
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

	/**
	 * Getter and setter for the local PathBuilderInterface instance.
	 *
	 * @param string|\Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface $name
	 * @param array $options
	 * @return \Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface
	 */
	public function pathBuilder($name = null, array $options = []) {
		if ($name instanceof PathBuilderInterface) {
			$this->_pathBuilder = $name;
			return $this->_pathBuilder;
		}
		if ($name !== null) {
			$this->_pathBuilder = $this->createPathBuilder($name, $options);
		}
		return $this->_pathBuilder;
	}

	/**
	 * Gets the path builder.
	 *
	 * @return \Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface
	 */
	public function getPathBuilder($name) {
		$this->pathBuilder($name);
	}

	/**
	 * Sets a path builder.
	 *
	 * @param string|\Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface $pathBuilder
	 * @param array Path builder options.
	 * @return void
	 */
	public function setPathBuilder($pathBuilder, array $options = []) {
		if (is_string($pathBuilder)) {
			$this->_pathBuilder = $this->createPathBuilder($pathBuilder, $options);
			return;
		}
		if (!$pathBuilder instanceof PathBuilderInterface) {
			throw new \InvalidArgumentException('The first arg is not a valid path builder object!');
		}
		$this->_pathBuilder = $pathBuilder;
	}
}
