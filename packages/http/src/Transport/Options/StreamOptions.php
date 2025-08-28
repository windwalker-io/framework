<?php

declare(strict_types=1);

namespace Windwalker\Http\Transport\Options;

class StreamOptions extends TransportOptions
{
    public function __construct(
        public array $context = [],
        public ?string $targetFile = null,
        bool $allowEmptyStatusCode = false,
        mixed $writeStream = null,
        ?int $timeout = null,
        ?string $userAgent = null,
        bool $followLocation = true,
        ?string $certpath = null,
        bool $verifyPeer = true,
        bool $optionMerged = false,
        ?array $files = [],
    ) {
        parent::__construct(
            optionMerged: $optionMerged,
            allowEmptyStatusCode: $allowEmptyStatusCode,
            writeStream: $writeStream,
            timeout: $timeout,
            userAgent: $userAgent,
            followLocation: $followLocation,
            certpath: $certpath,
            verifyPeer: $verifyPeer,
            files: $files
        );
    }
}
