<?php
declare(strict_types=1);
/**
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\PathBuilder;

use Burzum\FileStorage\Storage\StorageManager;
use Cake\Datasource\EntityInterface;

/**
 * The S3 Path Builder.
 */
class S3PathBuilder extends BasePathBuilder
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        $this->_defaultConfig['https'] = false;
        $this->_defaultConfig['modelFolder'] = true;
        $this->_defaultConfig['s3Url'] = 's3.amazonaws.com';
        parent::__construct($config);
    }

    /**
     * Gets the bucket from the adapter configuration.
     *
     * @param string $adapter Storage adapter config name.
     * @return string|null
     */
    protected function _getBucket($adapter): ?string
    {
        $config = StorageManager::config($adapter);

        return $config['adapterOptions'][1];
    }

    /**
     * Builds the cloud base URL for the given bucket and location.
     *
     * @param string $bucket Bucket name.
     * @param string|null $bucketPrefix Bucket prefix.
     * @param string|null $cfDist Optional param.
     * @return string
     */
    protected function _buildCloudUrl(string $bucket, ?string $bucketPrefix = null, ?string $cfDist = null): string
    {
        $path = $this->getConfig('https') === true ? 'https://' : 'http://';
        if ($cfDist) {
            $path .= $cfDist;
        } else {
            if ($bucketPrefix) {
                $path .= $bucket . '.' . $this->_config['s3Url'];
            } else {
                $path .= $this->_config['s3Url'] . '/' . $bucket;
            }
        }

        return $path;
    }

    /**
     * Builds the URL under which the file is accessible.
     *
     * This is for example important for S3 and Dropbox but also the Local adapter
     * if you symlink a folder to your webroot and allow direct access to a file.
     *
     * @param \Cake\Datasource\EntityInterface $entity Record entity.
     * @param array $options Options.
     * @return string
     */
    public function url(EntityInterface $entity, array $options = []): string
    {
        $bucket = $this->_getBucket($entity->get('adapter'));
        $pathPrefix = $this->ensureSlash($this->_buildCloudUrl($bucket), 'after');
        $path = parent::path($entity);
        $path = str_replace('\\', '/', $path);

        return $pathPrefix . $path . $this->filename($entity, $options);
    }
}
