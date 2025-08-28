<?php

declare(strict_types=1);

namespace Windwalker\Http\Transport\Options;

use Windwalker\Utilities\Options\RecordOptions;

#[\AllowDynamicProperties]
class TransportOptions extends RecordOptions
{
    public function __construct(
        public bool $optionMerged = false,
        public bool $allowEmptyStatusCode = false,
        public mixed $writeStream = null,
        public ?int $timeout = null,
        public ?string $userAgent = null,
        public bool $followLocation = true,
        public ?string $certpath = null,
        public bool $verifyPeer = true,
        public ?array $files = null,
    ) {
    }
}
