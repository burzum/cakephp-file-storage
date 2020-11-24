<?php

namespace TestApp\Storage\Processor;

use Phauthentic\Infrastructure\Storage\FileInterface;
use Phauthentic\Infrastructure\Storage\Processor\ProcessorInterface;

class ImageDimensionsProcessor implements ProcessorInterface
{
    /**
     * @var string
     */
    protected $root;

    /**
     * @param string|null $root
     */
    public function __construct(?string $root = null)
    {
        $this->root = $root ?: TMP;
    }

    /**
     * @inheritDoc
     */
    public function process(FileInterface $file): FileInterface
    {
        $path = $this->root . $file->path();
        if (!file_exists($path)) {
            throw new \RuntimeException('Cannot find file: ' . $path);
        }

        $dimensions = @getimagesize($path);
        if (!$dimensions) {
            return $file;
        }

        $file = $file->withMetadataKey('width', $dimensions[0])
            ->withMetadataKey('height', $dimensions[1]);

        return $file;
    }
}
