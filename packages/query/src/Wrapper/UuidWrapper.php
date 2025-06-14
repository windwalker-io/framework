<?php

declare(strict_types=1);

namespace Windwalker\Query\Wrapper;

use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ramsey\Uuid\Exception\InvalidBytesException;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Windwalker\Utilities\Wrapper\WrapperInterface;

/**
 * The UuidBinaryWrapper class.
 */
class UuidWrapper implements WrapperInterface, \Stringable
{
    protected UuidInterface $uuid;

    public function __construct(string|self|UuidInterface $uuid)
    {
        if ($uuid instanceof self) {
            $uuid = $uuid->getRaw();
        }

        if (is_string($uuid)) {
            $uuid = static::wrap($uuid);
        }

        $this->uuid = $uuid;
    }

    public function __invoke(mixed $src = null): mixed
    {
        return $this->uuid->toString();
    }

    public function getRaw(): UuidInterface
    {
        return $this->uuid;
    }

    public static function wrap(string|UuidInterface $uuid): UuidInterface
    {
        return static::tryWrap($uuid);
    }

    public static function tryWrap(string|UuidInterface|null $uuid): ?UuidInterface
    {
        if ($uuid === null || $uuid === '' || $uuid === '0x') {
            return null;
        }

        if ($uuid instanceof UuidInterface) {
            return $uuid;
        }

        try {
            if (strlen($uuid) === 36) {
                return Uuid::fromString($uuid);
            }

            return Uuid::fromBytes($uuid);
        } catch (InvalidArgumentException | InvalidBytesException | InvalidUuidStringException) {
            throw new \InvalidArgumentException(
                'Invalid UUID string or UUID bytes.',
            );
        }
    }

    public function __toString(): string
    {
        return $this->uuid->toString();
    }
}
