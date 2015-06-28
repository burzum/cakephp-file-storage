<?php
namespace Burzum\FileStorage\Storage\PathBuilder;

use Cake\ORM\Entity;

class LocalPathBuilder extends BasePathBuilder {

	public function __construct(array $config = []) {
		if (empty($config['tableFolder'])) {
			$config['tableFolder'] = true;
		}
		parent::__construct($config);
	}
}
