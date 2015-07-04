<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\PathBuilder;

use Burzum\FileStorage\Lib\StorageManager;
use Cake\ORM\Entity;

class S3PathBuilder extends BasePathBuilder {

	public function __construct(array $config = []) {
		if (empty($config['tableFolder'])) {
			$config['tableFolder'] = true;
		}
		parent::__construct($config);
	}

	protected function _getBucketFromAdapter($adapter) {
		$config = StorageManager::config($adapter);
		return $config['adapterOptions'][1];
	}

	protected function _buildCloudBaseUrl($protocol, $bucket, $bucketPrefix = null, $cfDist = null) {
		$path = $protocol . '://';
		if ($cfDist) {
			$path .= $cfDist;
		} else {
			if ($bucketPrefix) {
				$path .= $bucket . '.s3.amazonaws.com';
			} else {
				$path .= 's3.amazonaws.com/' . $bucket;
			}
		}
		return $path;
	}

/**
 * @todo finish me
 */
	public function url(Entity $entity, array $options = []) {
		$bucket = $this->_getBucketFromAdapter($entity->adapter);
		$pathPrefix = $this->_buildCloudBaseUrl('http', $bucket);
		$path = parent::path($entity);
		$path = str_replace('\\', '/', $path);
		return $pathPrefix . $path;
	}
}
