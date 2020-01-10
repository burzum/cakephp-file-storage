<?php
declare(strict_types=1);

/**
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\PathBuilder;

use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\EntityInterface;
use Cake\Utility\MergeVariablesTrait;
use InvalidArgumentException;

/**
 * A path builder is an utility class that generates a path and filename for a
 * file storage entity. All the fields from the entity can bed used to create
 * a path and file name.
 */
class BasePathBuilder implements PathBuilderInterface
{
    use InstanceConfigTrait;
    use MergeVariablesTrait;

    /**
     * Default settings.
     *
     * @var array
     */
    protected $_defaultConfig = [
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
        'modelFolder' => false,
        'sanitizeFilename' => false,
    ];

    /**
     * Constructor
     *
     * @param array $config Configuration options.
     */
    public function __construct(array $config = [])
    {
        $this->_mergeVars(
            ['_defaultConfig'],
            ['associative' => ['_defaultConfig']]
        );

        $this->setConfig($config);
    }

    /**
     * Strips dashes from a string
     *
     * @param string $uuid String to strip dashes out.
     * @return string String without the dashed
     */
    public function stripDashes($uuid): string
    {
        return str_replace('-', '', $uuid);
    }

    /**
     * Builds the path under which the data gets stored in the storage adapter.
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @param array $options
     * @return string
     */
    public function path(EntityInterface $entity, array $options = []): string
    {
        $config = array_merge($this->getConfig(), $options);
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
     * @param \Cake\Datasource\EntityInterface $entity Уntity.
     * @param string $path File path.
     * @param array $config Options.
     * @return string
     */
    protected function _pathPrefix(EntityInterface $entity, string $path, array $config): string
    {
        return $this->_pathPreAndSuffix($entity, $path, $config, 'prefix');
    }

    /**
     * Builds a path.
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @param string $path File path.
     * @param array $config Options.
     * @return string
     */
    protected function _path(EntityInterface $entity, string $path, array $config): string
    {
        if ($config['modelFolder'] === true) {
            $path .= $entity->get('model') . DS;
        }
        $id = $entity->get('id');
        if ($config['randomPath'] === true) {
            $path .= $this->randomPath($id);
        }
        if (is_string($config['randomPath']) && $id !== null) {
            $path .= $this->randomPath($id, 3, $config['randomPath']);
        }
        // uuidFolder for backward compatibility
        if ($config['uuidFolder'] === true || $config['idFolder'] === true) {
            $path .= $this->stripDashes($id) . DS;
        }

        return $path;
    }

    /**
     * Handles the path suffix generation.
     *
     * Overload this method as needed with your custom implementation.
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @param string $path File path.
     * @param array $config Options.
     * @return string
     */
    protected function _pathSuffix(EntityInterface $entity, string $path, array $config): string
    {
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
     * @param string $path
     * @param array $config
     * @param string $type
     * @return string
     */
    protected function _pathPreAndSuffix(EntityInterface $entity, string $path, array $config, $type = 'suffix'): string
    {
        $type = ucfirst($type);
        if (!in_array($type, ['Suffix', 'Prefix'])) {
            throw new InvalidArgumentException(sprintf('Invalid argument "%s" for $type!', $type));
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
     * @param bool $keepDot Keeps the dot in front of the extension.
     * @return array
     */
    public function splitFilename(string $filename, bool $keepDot = false): array
    {
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
    public function filename(EntityInterface $entity, array $options = []): string
    {
        $config = array_merge($this->getConfig(), $options);
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
    protected function _buildFilename(EntityInterface $entity, array $options = []): string
    {
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
    protected function _preserveFilename(EntityInterface $entity, array $options = []): string
    {
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
    public function fullPath(EntityInterface $entity, array $options = []): string
    {
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
    public function url(EntityInterface $entity, array $options = []): string
    {
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
    public function randomPath(string $string, int $level = 3, string $method = 'sha1'): string
    {
        if ($method === 'sha1') {
            return $this->_randomPathSha1($string, $level);
        }
        if (is_callable($method)) {
            return $method($string, $level);
        }
        if (method_exists($this, $method)) {
            return $this->{$method}($string, $level);
        }
        throw new InvalidArgumentException(sprintf('BasePathBuilder::randomPath() invalid hash `%s` method provided!', $method));
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
    protected function _randomPathSha1(string $string, int $level): string
    {
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
     * @param string|null $ds Directory separator should be / or \, if not set the DS constant is used.
     * @throws \InvalidArgumentException
     * @return string
     */
    public function ensureSlash(string $string, string $position, ?string $ds = null): string
    {
        if (!in_array($position, ['before', 'after', 'both'])) {
            $method = static::class . '::ensureSlash(): ';
            throw new InvalidArgumentException(sprintf($method . 'Invalid position `%s`!', $position));
        }
        if ($ds === null) {
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
