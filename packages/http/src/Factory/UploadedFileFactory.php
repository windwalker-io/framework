<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\Factory;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Windwalker\Http\UploadedFile;

/**
 * The UploadedFileFactory class.
 */
class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * Create a new uploaded file.
     *
     * If a size is not provided it will be determined by checking the size of
     * the file.
     *
     * @see http://php.net/manual/features.file-upload.post-method.php
     * @see http://php.net/manual/features.file-upload.errors.php
     *
     * @param  StreamInterface  $stream           Underlying stream representing the
     *                                            uploaded file content.
     * @param  int|null         $size             in bytes
     * @param  int              $error            PHP file upload error
     * @param  string|null      $clientFilename   Filename as provided by the client, if any.
     * @param  string|null      $clientMediaType  Media type as provided by the client, if any.
     *
     * @return UploadedFileInterface
     *
     */
    public function createUploadedFile(
        StreamInterface $stream,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ): UploadedFileInterface {
        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }
}
