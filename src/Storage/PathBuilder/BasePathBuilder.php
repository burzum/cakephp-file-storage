<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\PathBuilder;

use Cake\Core\InstanceConfigTrait;
use Cake\ORM\Entity;
use Burzum\FileStorage\Lib\FileStorageUtils;

/**
 * A path builder is an utility class that generates a path and filename for a
 * file storage entity.
 */
class BasePathBuilder implements PathBuilderInterface {

	use InstanceConfigTrait;

/**
 * Default settings.
 *
 * @var array
 */
	protected $_defaultConfig = array(
		'stripUuid' => true,
		'pathPrefix' => '',
		'pathSuffix' => '',
		'filePrefix' => '',
		'fileSuffix' => '',
		'preserveFilename' => false,
		'preserveExtension' => true,
		'uuidFolder' => true,
		'randomPath' => true,
		'modelFolder' => false
	);

/**
 * Constructor
 *
 * @param array $config Configuration options.
 */
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
 * @param Entity $entity
 * @param array $options
 * @return string
 */
	public function path(Entity $entity, array $options = []) {
		$config = array_merge($this->config(), $options);
		$path = '';
		if (!empty($config['pathPrefix']) && is_string($config['pathPrefix'])) {
			$path = $config['pathPrefix'] . DS . $path;
		}
		if ($this->_config['modelFolder'] === true) {
			$path .= $entity->model;
		}
		if ($this->_config['randomPath'] === true) {
			$path .= $this->randomPath($entity->id);
		}
		// uuidFolder for backward compatibility
		if ($this->_config['uuidFolder'] === true || $this->_config['idFolder'] === true) {
			$path .= $this->stripDashes($entity->id) . DS;
		}
		if (!empty($this->_config['pathSuffix']) && is_string($this->_config['pathSuffix'])) {
			$path = $path . $this->_config['pathSuffix'] . DS;
		}
		return $this->ensureSlash($path, 'after');
	}

/**
 * Splits the filename in name and extension.
 *
 * @param string $filename Filename to split in name and extension.
 * @param boolean $keepDot Keeps the dot in front of the extension.
 * @return array
 */
	public function splitFilename($filename, $keepDot = false) {
		$position = strrpos($filename, '.');
		if ($position === false) {
			$extension = '';
		} else {
			$extension = substr($filename, $position, strlen($filename));
			$filename = substr($filename, 0, $position);
			if ($keepDot === false) {
				$extension = substr($extension, 1);
			}
		}
		return compact('filename', 'extension');
	}

/**
 * Builds the filename of under which the data gets saved in the storage adapter.
 *
 * @param \Cake\ORM\Entity $entity
 * @param array $options
 * @return string
 */
	public function filename(Entity $entity, array $options = []) {
		$config = array_merge($this->config(), $options);
		if ($config['preserveFilename'] === true) {
			$filename = $entity['filename'];
			if (!empty($config['filePrefix'])) {
				$filename = $config['filePrefix'] . $entity['filename'];
			}
			if (!empty($config['fileSuffix'])) {
				$split = $this->splitFilename($filename, true);
				$filename = $split['filename'] . $config['fileSuffix'] . $split['extension'];
			}
			return $filename;
		}

		$filename = $entity->id;
		if ($config['stripUuid'] ===  true) {
			$filename = $this->stripDashes($filename);
		}
		if ($config['preserveExtension'] === true) {
			if (!empty($config['fileSuffix'])) {
				$filename = $filename . $config['fileSuffix'];
			}
			$filename = $filename . '.' . $entity['extension'];
		}
		if (!empty($config['filePrefix'])) {
			$filename = $config['filePrefix'] . $filename;
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
	public function fullPath(Entity $entity, array $options = []) {
		return $this->path($entity, $options) . $this->filename($entity, $options);
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
	public function url(Entity $entity, array $options = []) {
		$url = $this->path($entity) . $this->filename($entity);
		return str_replace('\\', '/', $url);
	}

/**
 * Creates a semi-random path based on a string.
 *
 * Makes it possible to overload this functionality.
 *
 * @param string $string Input string
 * @param int $level Depth of the path to generate.
 * @param string $method Hash method, crc32 or sha1.
 * @return string
 */
	public function randomPath($string, $level = 3, $method = 'sha1') {
		// Keeping this for backward compatibility but please stop using crc32()!
		if ($method === 'crc32') {
			return StorageUtils::randomPath($string);
		}
		if ($method === 'sha1') {
			$result = sha1($string);
			$randomString = '';
			$counter = 0;
			for ($i = 1; $i <= $level; $i++) {
				$counter = $counter + 2;
				$randomString .= substr($result, $counter, 2) . DS;
			}
			return $randomString;
		}
	}

/**
 * Ensures that a path has a leading and/or trailing (back-) slash.
 *
 * @param string $string
 * @param string $position Can be `before`, `after` or `both`
 * @param string $ds Directory separator should be / or \
 * @throws \InvalidArgumentException
 * @return string
 */
	public function ensureSlash($string, $position, $ds = null) {
		if (!in_array($position, ['before', 'after', 'both'])) {
			throw new \InvalidArgumentException(sprintf('Invalid position `%s`!', $position));
		}
		if (is_null($ds)) {
			$ds = DS;
		}
		if ($position === 'before' || $position === 'both') {
			if (strpos($string, $ds) !== 0) {
				$string = $ds . $string;
			}
		}
		if ($position === 'after' || $position === 'both') {
			if (substr($string, -1, 1) !== $ds ) {
				$string = $string . $ds;
			}
		}
		return $string;
	}
}
