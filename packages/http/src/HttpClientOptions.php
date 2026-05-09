<?php

declare(strict_types=1);

namespace Windwalker\Http;

use Windwalker\Http\Transport\Options\TransportOptions;
use Windwalker\Http\Transport\ProgressEvent;
use Windwalker\Utilities\Options\RecordOptions;

class HttpClientOptions extends RecordOptions
{
    public ?TransportOptions $transport = null;

    public function __construct(
        TransportOptions|array|null $transport = null,
        public ?string $baseUri = null,
        public ?array $vars = null,
        public ?array $headers = null,
        public ?array $params = null,
        public ?array $files = null,
        public mixed $writeStream = null,
        public ?int $timeout = null,
        public ?string $userAgent = null,
        public bool $followLocation = true,
        public ?string $certpath = null,
        public bool $verifyPeer = true,
        /**
         * @var \Closure(ProgressEvent $event): void|null
         */
        public ?\Closure $progress = null,
    ) {
        $this->transport = TransportOptions::tryWrap($transport);
    }
}
