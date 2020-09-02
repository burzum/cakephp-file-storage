<?php
declare(strict_types=1);

namespace Burzum\FileStorage\FileStorage;

use Cake\Datasource\EntityInterface;
use Phauthentic\Infrastructure\Storage\FileInterface;

interface DataTransformerInterface
{
    /**
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @return \Phauthentic\Infrastructure\Storage\FileInterface File
     */
    public function entityToFileObject(EntityInterface $entity): FileInterface;

    /**
     * @param \Phauthentic\Infrastructure\Storage\FileInterface $file File
     * @param \Cake\Datasource\EntityInterface|null
     * @return \Cake\Datasource\EntityInterface
     */
    public function fileObjectToEntity(FileInterface $file, ?EntityInterface $entity): EntityInterface;
}
