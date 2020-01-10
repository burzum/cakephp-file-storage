<?php
declare(strict_types=1);

namespace Burzum\FileStorage\Storage;

use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;

/**
 * StorageManager - manages and instantiates Gaufrette storage engine instances
 *
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
class StorageManager
{
    public const GAUFRETTE_ENGINE = 'Gaufrette';
    public const FLYSYSTEM_ENGINE = 'Flysystem';

    /**
     * Adapter configurations
     *
     * @var array
     */
    protected $_adapterConfig = [
        'Local' => [
            'adapterOptions' => [TMP, true],
            'adapterClass' => '\Gaufrette\Adapter\Local',
            'class' => '\Gaufrette\Filesystem',
        ],
    ];

    /**
     * Return a singleton instance of the StorageManager.
     *
     * @return self
     */
    public static function &getInstance(): self
    {
        static $instance = [];
        if (!$instance) {
            $instance[0] = new self();
        }

        return $instance[0];
    }

    /**
     * Gets the configuration array for an adapter.
     *
     * @param string $adapter
     * @param array $options
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public static function config(string $adapter, array $options = [])
    {
        $_this = static::getInstance();
        if (!empty($adapter) && !empty($options)) {
            return $_this->_adapterConfig[$adapter] = $options;
        }

        if (isset($_this->_adapterConfig[$adapter])) {
            return $_this->_adapterConfig[$adapter];
        }

        return null;
    }

    /**
     * Flush all or a single adapter from the config.
     *
     * @param string|null $name Config name, if none all adapters are flushed.
     * @return bool True on success.
     */
    public static function flush(?string $name = null): bool
    {
        $_this = static::getInstance();

        if (isset($_this->_adapterConfig[$name])) {
            unset($_this->_adapterConfig[$name]);

            return true;
        }

        return false;
    }

    /**
     * Returns a list of cf the configurations loaded into the manager
     *
     * @return array
     */
    public static function getConfigList(): array
    {
        $_this = static::getInstance();

        return array_keys($_this->_adapterConfig);
    }

    /**
     * Gets a configured instance of a storage adapter.
     *
     * @param string $configName string of adapter configuration or array of settings
     * @param bool|bool $renewObject Creates a new instance of the given adapter in the configuration
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     * @return mixed filesystem
     */
    public static function get(string $configName, bool $renewObject = false)
    {
        if (empty($configName) || !is_string($configName)) {
            throw new InvalidArgumentException('StorageManager::get() first arg must be a non empty string!');
        }

        $_this = static::getInstance();

        if (!empty($_this->_adapterConfig[$configName])) {
            $adapter = $_this->_adapterConfig[$configName];
        } else {
            throw new RuntimeException(sprintf('Invalid Storage Adapter %s!', $configName));
        }

        if (!empty($_this->_adapterConfig[$configName]['object']) && $renewObject === false) {
            return $_this->_adapterConfig[$configName]['object'];
        }

        $engineObject = $_this->_factory($adapter);

        $_this->_adapterConfig[$configName]['object'] = &$engineObject;

        return $engineObject;
    }

    /**
     * Switches between the engines
     *
     * @param array $adapter Adapter config
     * @return mixed
     * @throws \ReflectionException
     */
    protected function _factory(array $adapter)
    {
        $_this = static::getInstance();

        if (!isset($adapter['engine'])) {
            $adapter['engine'] = 'Gaufrette';
        }
        if ($adapter['engine'] === static::GAUFRETTE_ENGINE) {
            return $_this->gaufretteFactory($adapter);
        }
        if ($adapter['engine'] === static::FLYSYSTEM_ENGINE) {
            return $_this->flysystemFactory($adapter);
        }

        throw new RuntimeException();
    }

    /**
     * Instantiates Gaufrette adapters.
     *
     * @param array $adapter
     * @return object
     * @throws \ReflectionException
     */
    public static function gaufretteFactory(array $adapter)
    {
        $class = $adapter['adapterClass'];
        $Reflection = new ReflectionClass($class);

        if (!is_array($adapter['adapterOptions'])) {
            throw new InvalidArgumentException('The adapter options must be an array!');
        }

        $adapterObject = $Reflection->newInstanceArgs($adapter['adapterOptions']);

        return new $adapter['class']($adapterObject);
    }

    /**
     * Instantiates Flystem adapters.
     *
     * @param array $adapter
     * @return object
     * @throws \ReflectionException
     */
    public static function flysystemFactory(array $adapter)
    {
        if (class_exists($adapter['adapterClass'])) {
            return (new ReflectionClass($adapter['adapterClass']))->newInstanceArgs($adapter['adapterOptions']);
        }

        $leagueAdapter = '\\League\\Flysystem\\Adapter\\' . $adapter['adapterClass'];
        if (class_exists($leagueAdapter)) {
            return (new ReflectionClass($leagueAdapter))->newInstanceArgs($adapter['adapterOptions']);
        }

        $leagueAdapter = '\\League\\Flysystem\\' . $adapter['adapterClass'] . '\\' . $adapter['adapterClass'] . 'Adapter';
        if (class_exists($leagueAdapter)) {
            return (new ReflectionClass($leagueAdapter))->newInstanceArgs($adapter['adapterOptions']);
        }

        throw new InvalidArgumentException('Unknown adapter');
    }
}
