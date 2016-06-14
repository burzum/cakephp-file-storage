<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\PathBuilder;

use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\EntityInterface;
use Cake\Utility\MergeVariablesTrait;

/**
 * A path builder is an utility class that generates a path and filename for a
 * file storage entity.
 */
class BasePathBuilder implements PathBuilderInterface {

	use InstanceConfigTrait;
	use MergeVariablesTrait;

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
		'uuidFolder' => false, // Backward compatibility option, use idFolder
		'idFolder' => true,
		'randomPath' => 'sha1',
		'modelFolder' => false
	);

	/**
	 * Constructor
	 *
	 * @param array $config Configuration options.
	 */
	public function __construct(array $config = []) {
		$this->_mergeVars(
			['_defaultConfig'],
			['associative' => ['_defaultConfig']]
		);
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
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return string
	 */
	public function path(EntityInterface $entity, array $options = []) {
		$config = array_merge($this->config(), $options);
		$path = '';
		$path = $this->_pathPrefix($entity, $path, $config);
		$path = $this->_path($entity, $path, $config);
		$path = $this->_pathSuffix($entity, $path, $config);
		return $this->ensureSlash($path, 'after');
	}

	/**
	 * Handles the path prefix generation.
	 *
	 * Overload this method as needed with your custom implementation.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param $path string
	 * @param $config array
	 * @return string
	 */
	protected function _pathPrefix(EntityInterface $entity, $path, array $config) {
		return $this->_pathPreAndSuffix($entity, $path, $config, 'prefix');
	}

	/**
	 * Builds a path.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param string $path
	 * @param array $config
	 * @return string
	 */
	protected function _path(EntityInterface $entity, $path, array $config) {
		if ($config['modelFolder'] === true) {
			$path .= $entity->model . DS;
		}
		if ($config['randomPath'] === true) {
			$path .= $this->randomPath($entity->id);
		}
		if (is_string($config['randomPath'])) {
			$path .= $this->randomPath($entity->id, 3, $config['randomPath']);
		}
		// uuidFolder for backward compatibility
		if ($config['uuidFolder'] === true || $config['idFolder'] === true) {
			$path .= $this->stripDashes($entity->id) . DS;
		}
		return $path;
	}

	/**
	 * Handles the path suffix generation.
	 *
	 * Overload this method as needed with your custom implementation.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param $path string
	 * @param $config array
	 * @return string
	 */
	protected function _pathSuffix(EntityInterface $entity, $path, array $config) {
		return $this->_pathPreAndSuffix($entity, $path, $config, 'suffix');
	}

	/**
	 * Handles the path suffix generation.
	 *
	 * By default prefix and suffix are handled the same but just use a different
	 * config array key. This methods handles both and just changes the config
	 * key conditionally. Overload _pathSuffix() and _pathPrefix() for your custom
	 * implementation instead of touching this methods.
	 *
	 * @see BasePathBuilder::_pathSuffix()
	 * @see BasePathBuilder::_pathPrefix()
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param $path string
	 * @param $config array
	 * @param $type string
	 * @return string
	 */
	protected function _pathPreAndSuffix(EntityInterface $entity, $path, array $config, $type = 'suffix') {
		$type = ucfirst($type);
		if (!in_array($type, ['Suffix', 'Prefix'])) {
			throw new \InvalidArgumentException(sprintf('Invalid argument "%s" for $type!', $type));
		}
		$type = 'path' . $type;
		if (!empty($config[$type]) && is_string($config[$type])) {
			$path = $path . $config[$type] . DS;
		}
		if (!empty($config[$type]) && is_callable($config[$type])) {
			$path = $config[$type]($entity, $path);
		}
		return $path;
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
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return string
	 */
	public function filename(EntityInterface $entity, array $options = []) {
		$config = array_merge($this->config(), $options);
		if ($config['preserveFilename'] === true) {
			return $this->_preserveFilename($entity, $config);
		}
		return $this->_buildFilename($entity, $config);
	}

	/**
	 * Used to build a completely customized filename.
	 *
	 * The default behavior is to use the UUID from the entities primary key to
	 * generate a filename based of the UUID that gets the dashes stripped and the
	 * extension added if you configured the path builder to preserve it.
	 *
	 * The filePrefix and fileSuffix options are also supported.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return string
	 */
	protected function _buildFilename(EntityInterface $entity, array $options = []) {
		$filename = $entity->id;
		if ($options['stripUuid'] === true) {
			$filename = $this->stripDashes($filename);
		}
		if (!empty($options['fileSuffix'])) {
			$filename = $filename . $options['fileSuffix'];
		}
		if ($options['preserveExtension'] === true) {
			$filename = $filename . '.' . $entity['extension'];
		}
		if (!empty($options['filePrefix'])) {
			$filename = $options['filePrefix'] . $filename;
		}
		return $filename;
	}

	/**
	 * Keeps the original filename but is able to inject pre- and suffix.
	 *
	 * This can be useful to create versions of files for example.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return string
	 */
	protected function _preserveFilename(EntityInterface $entity, array $options = []) {
		$filename = $entity['filename'];
		if (!empty($options['filePrefix'])) {
			$filename = $options['filePrefix'] . $entity['filename'];
		}
		if (!empty($options['fileSuffix'])) {
			$split = $this->splitFilename($filename, true);
			$filename = $split['filename'] . $options['fileSuffix'];
			if ($options['preserveExtension'] === true) {
				$filename .= $split['extension'];
			}
		}
		return $filename;
	}

	/**
	 * Returns the path + filename.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return string
	 */
	public function fullPath(EntityInterface $entity, array $options = []) {
		return $this->path($entity, $options) . $this->filename($entity, $options);
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
		$url = $this->path($entity, $options) . $this->filename($entity, $options);
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
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public function randomPath($string, $level = 3, $method = 'sha1') {
		// Keeping this for backward compatibility but please stop using crc32()!
		if ($method === 'crc32') {
			return $this->_randomPathCrc32($string, $level);
		}
		if ($method === 'sha1') {
			return $this->_randomPathSha1($string, $level);
		}
		if (is_callable($method)) {
			return $method($string, $level);
		}
		if (method_exists($this, $method)) {
			return $this->{$method}($string, $level);
		}
		throw new \InvalidArgumentException(sprintf('BasepathBuilder::randomPath() invalid hash `%s` method provided!', $method));
	}

	/**
	 * Creates a semi-random path based on a string.
	 *
	 * Please STOP USING CR32! See the huge warning on the php documentation page.
	 * of the crc32() function.
	 *
	 * @deprecated Stop using it, see the methods doc block for more info.
	 * @link http://php.net/manual/en/function.crc32.php
	 * @link https://www.box.com/blog/crc32-checksums-the-good-the-bad-and-the-ugly/
	 * @param string $string Input string
	 * @param int $level Depth of the path to generate.
	 * @return string
	 */
	protected function _randomPathCrc32($string, $level) {
		$string = crc32($string);
		$decrement = 0;
		$path = null;
		for ($i = 0; $i < $level; $i++) {
			$decrement = $decrement - 2;
			$path .= sprintf("%02d" . DS, substr(str_pad('', 2 * $level, '0') . $string, $decrement, 2));
		}
		return $path;
	}

	/**
	 * Creates a semi-random path based on a string.
	 *
	 * Makes it possible to overload this functionality.
	 *
	 * @param string $string Input string
	 * @param int $level Depth of the path to generate.
	 * @return string
	 */
	protected function _randomPathSha1($string, $level) {
		$result = sha1($string);
		$randomString = '';
		$counter = 0;
		for ($i = 1; $i <= $level; $i++) {
			$counter = $counter + 2;
			$randomString .= substr($result, $counter, 2) . DS;
		}
		return $randomString;
	}

	/**
	 * Ensures that a path has a leading and/or trailing (back-) slash.
	 *
	 * @param string $string
	 * @param string $position Can be `before`, `after` or `both`
	 * @param string $ds Directory separator should be / or \, if not set the DS constant is used.
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public function ensureSlash($string, $position, $ds = null) {
		if (!in_array($position, ['before', 'after', 'both'])) {
			$method = get_class($this) . '::ensureSlash(): ';
			throw new \InvalidArgumentException(sprintf($method . 'Invalid position `%s`!', $position));
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
			if (substr($string, -1, 1) !== $ds) {
				$string = $string . $ds;
			}
		}
		return $string;
	}
}
