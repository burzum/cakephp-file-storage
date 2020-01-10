<?php
declare(strict_types=1);

namespace Burzum\FileStorage\Model\Entity;

use Cake\Core\Configure;
use InvalidArgumentException;

/**
 * FileStorage Entity.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2017 Florian Krämer
 * @license MIT
 */
class ImageStorage extends FileStorage
{
    /**
     * Gets the version of an image.
     *
     * @param string $version Version name.
     * @param array $options Options parameters.
     * @return string
     */
    public function imageVersion($version, array $options = []): string
    {
        $options['version'] = $version;
        $options['image'] = $this;
        $options['hash'] = Configure::read('FileStorage.imageHashes.' . $this->get('model') . '.' . $version);

        if (empty($options['hash'])) {
            throw new InvalidArgumentException(sprintf('No valid version key (Identifier: `%s` Key: `%s`) passed!', $this->get('model'), $version));
        }

        $event = $this->dispatchEvent('ImageVersion.getVersions', $options);

        return $event->getResult();
    }
}
