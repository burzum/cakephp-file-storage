<?php
declare(strict_types=1);

namespace Burzum\FileStorage\Model\Entity;

use Cake\Datasource\EntityInterface;

/**
 * FileStorage Entity.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2020 Florian Krämer
 * @license MIT
 */
interface FileStorageEntityInterface extends EntityInterface
{
    /**
     * @param string $variant Variant
     * @return string|null
     */
    public function getVariantUrl(string $variant): ?string;

    /**
     * @param string $variant Variant
     * @return string|null
     */
    public function getVariantPath(string $variant): ?string;
}
