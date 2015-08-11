<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\PathBuilder;

use Burzum\FileStorage\Storage\StorageTrait;
use Cake\Datasource\EntityInterface;

class LocalPathBuilder extends BasePathBuilder {

	use StorageTrait;

/**
 * Constructor
 *
 * Default options for compatibility reasons:
 * - pathPrefix = 'files'
 * - randomPath = 'crc32'
 *
 * @param array $config Configuration options.
 */
	public function __construct(array $config = []) {
		$this->_defaultConfig['pathPrefix'] = 'files';
		$this->_defaultConfig['randomPath'] = 'crc32';
		parent::__construct($config);
	}

/**
 * Returns the full filsystem path as defined in the storage adapter including filename.
 *
 * @param \Cake\Datasource\EntityInterface $entity
 * @param array $options
 * @return string
 */
	public function fullPath(EntityInterface $entity, array $options = []) {
		$config = array_merge($this->config(), $options);

		$storageConfig = $this->storageConfig($entity->get('adapter'));
		return $storageConfig['adapterOptions'][0] . parent::fullPath($entity, $config);
	}
}
