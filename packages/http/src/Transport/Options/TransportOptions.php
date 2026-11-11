<?php

declare(strict_types=1);

namespace Windwalker\Http\Transport\Options;

use AllowDynamicProperties;
use Closure;
use Windwalker\Http\HttpClientOptions;
use Windwalker\Http\Transport\ProgressEvent;
use Windwalker\Utilities\Options\RecordOptions;

#[AllowDynamicProperties]
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
        /**
         * @var  Closure(ProgressEvent $event): void|null
         */
        public ?Closure $progress = null,
        /**
         * Max allowed response body size in bytes. The transport aborts the download
         * once the received body exceeds this size, to avoid memory exhaustion from
         * unexpectedly huge responses. `null` means no limit.
         */
        public ?int $maxResponseSize = null,
        /**
         * Receives each downloaded chunk of the response body. When set, the body is not
         * buffered into the response unless `$writeStream` is also given. Return `false`
         * to abort the download.
         *
         * @var  Closure(string $chunk): (void|bool)|null
         */
        public ?Closure $writeHandler = null,
    ) {}

    public static function fromHttpClientOptions(HttpClientOptions $options): static
    {
        $transportOptions = $options->transport ?? new static();

        return $transportOptions->withMergeHttpClientOptions($options);
    }

    public function withMergeHttpClientOptions(HttpClientOptions $options): static
    {
        $new = clone $this;
        $new->files = $options->files;
        $new->progress = $options->progress;
        $new->timeout = $options->timeout;
        $new->userAgent = $options->userAgent;
        $new->followLocation = $options->followLocation;
        $new->certpath = $options->certpath;
        $new->verifyPeer = $options->verifyPeer;
        $new->maxResponseSize = $options->maxResponseSize;
        $new->writeStream = $options->writeStream ?? $new->writeStream;
        $new->writeHandler = $options->writeHandler ?? $new->writeHandler;

        $new->optionMerged = true;

        return $new;
    }
}
