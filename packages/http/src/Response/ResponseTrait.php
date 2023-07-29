<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\Response;

use Windwalker\Data\Collection;
use Windwalker\Http\Helper\ResponseHelper;

/**
 * Trait ResponseTrait
 */
trait ResponseTrait
{
    public function isSuccess(): bool
    {
        return ResponseHelper::isSuccess($this->getStatusCode());
    }

    public function isServerError(): bool
    {
        return ResponseHelper::isServerError($this->getStatusCode());
    }

    public function isAuthError(): bool
    {
        return ResponseHelper::isClientError($this->getStatusCode());
    }

    public function isRedirect(): bool
    {
        return ResponseHelper::isRedirect($this->getStatusCode());
    }

    public function getContent(): string
    {
        return (string) $this->getBody();
    }

    /**
     * @throws \JsonException
     */
    public function jsonDecode(?bool $associative = null, int $depth = 512, int $flags = 0): mixed
    {
        return json_decode($this->getContent(), $associative, $depth, JSON_THROW_ON_ERROR | $flags);
    }

    public function decode(string $format = 'json', array $options = []): Collection
    {
        return Collection::from($this->getContent(), $format, $options);
    }

    public function toArray(string $format = 'json', array $options = []): array
    {
        return $this->decode($format, $options)->dump();
    }
}
