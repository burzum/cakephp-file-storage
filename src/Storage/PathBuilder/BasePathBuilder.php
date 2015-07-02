<?php
namespace Burzum\FileStorage\Storage\PathBuilder;

use Cake\Core\InstanceConfigTrait;
use Cake\ORM\Entity;
use Burzum\FileStorage\Lib\FileStorageUtils;

/**
 * A path builder is an utility class that generates a path and filename for a
 * file storage entity.
 */
class BasePathBuilder {

	use InstanceConfigTrait;

/**
 * Default settings
 *
 * @var array
 */
	protected $_defaultConfig = array(
		'models' => false,
		'stripUuid' => true,
		'preserveFilename' => false,
		'preserveExtension' => true,
		'uuidFolder' => true,
		'randomPath' => true,
		'tableFolder' => false,
		'modelFolder' => false
	);

	public function __construct(array $config = []) {
		$this->config($config);
	}

/**
 * Strips dashes from a string
 *
 * @param string
 * @return string String without the dashed
 */
	public function stripDashes($uuid) {
		return str_replace('-', '', $uuid);
	}

/**
 * Builds the path under which the data gets stored in the storage adapter.
 *
 * @param Table $table
 * @param Entity $entity
 * @return string
 */
	public function path($entity, array $options = []) {
		$path = '';
		if ($this->_config['tableFolder'] && is_string($this->_config['tableFolder'])) {
			$path .= $this->_config['tableFolder'] . DS;
		}
		if ($this->_config['modelFolder'] === true) {
			$path .= $entity->model;
		}
		if ($this->_config['randomPath'] === true) {
			$path .= FileStorageUtils::randomPath($entity->id);
		}
		if ($this->_config['uuidFolder'] === true) {
			$path .= $this->stripDashes($entity->id) . DS;
		}
		return $path;
	}

/**
 * Builds the filename of under which the data gets saved in the storage adapter.
 *
 * @param \Cake\ORM\Entity $entity
 * @param array $options
 * @return string
 */
	public function filename($entity, array $options = []) {
		if ($this->_config['preserveFilename'] === true) {
			return $entity['filename'];
		}
		$filename = $entity['id'];
		if ($this->_config['stripUuid'] ===  true) {
			$filename = $this->stripDashes($filename);
		}
		if ($this->_config['preserveExtension'] === true) {
			$filename = $filename . '.' . $entity['extension'];
		}
		return $filename;
	}

/**
 * Returns the path + filename.
 *
 * @param \Cake\ORM\Entity $entity
 * @param array $options
 * @return string
 */
	public function fullPath($entity, array $options = []) {
		return $this->path($entity) . $this->filename($entity);
	}

/**
 * Builds the URL under which the file is accessible.
 *
 * This is for example important for S3 and Dropbox but also the Local adapter
 * if you symlink a folder to your webroot and allow direct access to a file.
 *
 * @param \Cake\ORM\Entity $entity
 * @param array $options
 * @return string
 */
	public function url($entity, array $options = []) {
		$url = $this->path($entity) . $this->filename($entity);
		return str_replace('\\', '/', $url);
	}

/**
 * Proxy to FileStorageUtils::randomPath.
 *
 * Makes it possible to overload this functionality.
 *
 * @param string $string
 * @return string
 */
	public function randomPath($string) {
		return FileStorageUtils::randomPath($string);
	}

}
