<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

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
        return $this->uuid->getBytes();
    }

    public function getRaw(): UuidInterface
    {
        return $this->uuid;
    }

    public static function wrap(string|UuidInterface $uuid): UuidInterface
    {
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
        return $this->uuid->getBytes();
    }
}
