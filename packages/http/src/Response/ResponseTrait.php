<?php

declare(strict_types=1);

namespace Windwalker\Http\Response;

use Windwalker\Data\Collection;
use Windwalker\Http\Exception\HttpRequestException;
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

    public function isError(): bool
    {
        return $this->isServerError() || $this->isAuthError();
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

    /**
     * @template T of class-string<\Exception>
     *
     * @param  string  $className
     *
     * @return  T
     */
    public function toException(string $className = HttpRequestException::class): \Exception
    {
        $e = new $className($this->getReasonPhrase(), $this->getStatusCode());

        if ($e instanceof HttpRequestException) {
            $e = $e->withResponse($this);
        }

        return $e;
    }

    /**
     * @template T of class-string<\Exception>
     *
     * @param  string  $className
     *
     * @return  never
     *
     * @throws T
     */
    public function throw(string $className = HttpRequestException::class): never
    {
        throw $this->toException($className);
    }

    /**
     * @template T of class-string<\Exception>
     *
     * @param  string  $className
     *
     * @return  void
     *
     * @throws T
     */
    public function throwIfError(string $className = HttpRequestException::class): void
    {
        if ($this->isError()) {
            throw $this->toException($className);
        }
    }
}
