<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\PathBuilder;

use Cake\ORM\Entity;

class LocalPathBuilder extends BasePathBuilder {

/**
 * Constructor
 *
 * @param array $config
 */
	public function __construct(array $config = array()) {
		$this->_defaultConfig['modelFolder'] = true;
		parent::__construct($config);
	}
}
