<?php

declare(strict_types=1);

namespace Windwalker\Http\File;

/**
 * Interface HttpUploadFileInterface
 */
interface HttpUploadFileInterface
{
    public function getMimeType(): ?string;

    public function getPostFilename(): ?string;

    public function setMimeType(?string $mime): static;

    public function setPostFilename(?string $postname): static;

    public function toCurlFile(): object;

    public function getContent(): string;
}
