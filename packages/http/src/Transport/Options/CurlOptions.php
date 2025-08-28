<?php

declare(strict_types=1);

namespace Windwalker\Http\Transport\Options;

class CurlOptions extends TransportOptions
{
    public function __construct(
        public bool $ignoreCurlError = false,
        public bool $autoCalcContentLength = true,
        /**
         * @deprecated  Use $curl instead.
         */
        public array $options = [],
        public array $curl = [],
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
            files: $files,
        );
    }
}
