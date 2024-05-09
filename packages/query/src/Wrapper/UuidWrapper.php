<?php

declare(strict_types=1);

namespace Windwalker\Query\Wrapper;

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
        if ($uuid === null || $uuid === '') {
            return null;
        }

        if ($uuid instanceof UuidInterface) {
            return $uuid;
        }

        if (strlen($uuid) === 36) {
            return Uuid::fromString($uuid);
        }

        return Uuid::fromBytes($uuid);
    }

    public function __toString()
    {
        return $this->uuid->toString();
    }
}
