<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\PathBuilder;

use Burzum\FileStorage\Storage\StorageManager;
use Cake\Datasource\EntityInterface;

class S3PathBuilder extends BasePathBuilder {

	public function __construct(array $config = []) {
		$config += [
			'https' => true,
			'modelFolder' => true,
			'baseUrl' => false,
		];
		parent::__construct($config);
	}

/**
 * Checks if a subdirectory has been set in the adapter settings.
 *
 * Used to add to the URL functions.
 *
 * @param string $adapter Adapter config name set in the StorageManager
 * @return string
 */
	protected function _adapterSubDirectory($adapter) {
		$config = StorageManager::config($adapter);
		if (isset($config['adapterOptions'][2]['directory'])) {
			return $config['adapterOptions'][2]['directory'] . '/';
		}
		return '';
	}

	protected function _getBucket($adapter) {
		$config = StorageManager::config($adapter);
		return $config['adapterOptions'][1];
	}

	protected function _getRegion($adapter) {
		$config = StorageManager::config($adapter);
		$S3Client = $config['adapterOptions'][0];
		return $S3Client->getRegion();
	}

	protected function _buildCloudUrl($adapter, $bucketPrefix = null, $cfDist = null) {
		$path = $this->config('https') === true ? 'https://' : 'http://';
		$path .= 's3-' . $this->_getRegion($adapter) . '.amazonaws.com/' . $this->_getBucket($adapter) . '/';
		return $path;
	}

/**
 * Builds the URL under which the file is accessible.
 *
 * This is for example important for S3 and Dropbox but also the Local adapter
 * if you symlink a folder to your webroot and allow direct access to a file.
 *
 * @param \Cake\Datasource\EntityInterface $entity
 * @param array $options
 * @return string
 */
	public function url(EntityInterface $entity, array $options = []) {
		if (($baseUrl = $this->config('baseUrl')) === false) {
			$baseUrl = $this->_buildCloudUrl($entity->adapter);
		}
		$subDirectory = $this->_adapterSubDirectory($entity->adapter);
		$path = $subDirectory . $this->path($entity) . $this->filename($entity);
		$path = str_replace('\\', '/', $path);
		$path = ltrim($path, '/');
		return $baseUrl . $path;
	}
}
