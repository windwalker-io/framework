<?php

declare(strict_types=1);

namespace Windwalker\Http\Transport;

use Asika\BetterUnits\FileSize;

class ProgressEvent
{
    public FileSize $downloadTotalFileSize {
        get => self::wrapFileSize($this->downloadTotal);
    }

    public FileSize $downloadedFileSize {
        get => self::wrapFileSize($this->downloaded);
    }

    public FileSize $uploadTotalFileSize {
        get => self::wrapFileSize($this->uploadTotal);
    }

    public FileSize $uploadedFileSize {
        get => self::wrapFileSize($this->uploaded);
    }

    public function __construct(
        readonly public \CurlHandle $handle,
        readonly public float $downloadTotal,
        readonly public float $downloaded,
        readonly public float $uploadTotal,
        readonly public float $uploaded,
        readonly public array $info = [],
    ) {
    }

    public static function wrapFileSize(float $size): FileSize
    {
        if (!class_exists(FileSize::class)) {
            throw new \RuntimeException('Please install `asika/better-units` to use FileSize object.');
        }

        return new FileSize($size, FileSize::UNIT_BYTES)->withOnlyBytesBinary();
    }
}
