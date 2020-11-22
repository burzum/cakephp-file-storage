<?php

declare(strict_types = 1);

namespace Burzum\FileStorage\Model\Entity;

use Cake\ORM\Entity;

/**
 * FileStorage Entity.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2020 Florian Krämer
 * @license MIT
 *
 * @property array $variants
 * @property array $metadata
 * @property int $id
 * @property int|null $user_id
 * @property int|null $foreign_key
 * @property string|null $model
 * @property string|null $filename
 * @property int|null $filesize
 * @property string|null $mime_type
 * @property string|null $extension
 * @property string|null $hash
 * @property string|null $path
 * @property string|null $adapter
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property string|null $collection
 * @property array $variant_urls
 */
class FileStorage extends Entity implements FileStorageEntityInterface
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * @var array
     */
    protected $_virtual = [
        'variantUrls',
    ];

    /**
     * @param string $variant Variant
     *
     * @return string|null
     */
    public function getVariantUrl(string $variant): ?string
    {
        $variants = (array)$this->get('variants');
        if (!isset($variants[$variant]['url'])) {
            return null;
        }

        return $variants[$variant]['url'];
    }

    /**
     * @param string $variant Variant
     *
     * @return string|null
     */
    public function getVariantPath(string $variant): ?string
    {
        $variants = (array)$this->get('variants');
        if (!isset($variants[$variant]['path'])) {
            return null;
        }

        return $variants[$variant]['path'];
    }

    /**
     * Making it backward compatible
     *
     * @return array
     */
    protected function _getVariantUrls()
    {
        $variants = (array)$this->get('variants');
        $list = [
            'original' => $this->get('url'),
        ];

        foreach ($variants as $name => $data) {
            if (!empty($data['url'])) {
                $list[$name] = $data['url'];
            }
        }

        return $list;
    }
}
