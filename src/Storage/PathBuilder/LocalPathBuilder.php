<?php
namespace Burzum\FileStorage\Storage\PathBuilder;

use Cake\ORM\Entity;

class LocalPathBuilder extends BasePathBuilder {

	public function __construct(Entity $entity, array $config = []) {
		if (empty($config['tableFolder'])) {
			$config['tableFolder'] = true;
		}
		parent::__construct($entity, $config);
		$this->_entity = $entity;
		$this->config($config);
	}
}
