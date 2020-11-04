<?php declare(strict_types = 1);

namespace Burzum\FileStorage\Utility;

use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;

class StorageUtils
{
    /**
     * @param string $filename
     * @param string|null $mimeType
     *
     * @return \Psr\Http\Message\UploadedFileInterface
     */
    public static function fileToUploadedFileObject(string $filename, ?string $mimeType = null): UploadedFileInterface
    {
        return new UploadedFile(
            $filename,
            (int)filesize($filename),
            UPLOAD_ERR_OK,
            basename($filename),
            $mimeType
        );
    }

    /**
     * @param string $filename
     * @param string|null $mimeType
     *
     * @return array
     */
    public static function fileToUploadedFileArray(string $filename, ?string $mimeType = null): array
    {
        return [
            'tmp_name' => $filename,
            'size' => filesize($filename),
            'error' => UPLOAD_ERR_OK,
            'name' => basename($filename),
            'type' => $mimeType,
        ];
    }
}
