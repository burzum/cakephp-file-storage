<?php
namespace Burzum\FileStorage\Storage\PathBuilder;

/**
 * @author Florian Krämer
 * @author Robert Pustułka
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
trait PathBuilderTrait {

	/**
	 * Local PathBuilderInterface instance.
	 *
	 * @var \Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface
	 */
	protected $_pathBuilder;

	/**
	 * Builds the path builder for given name and options.
	 *
	 * @param string $name
	 * @param array $options
	 * @param bool $renewObject
	 * @return \Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface
	 */
	public function createPathBuilder($name, array $options = []) {
		return PathBuilderFactory::create($name, $options);
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
