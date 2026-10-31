<?php

declare(strict_types=1);

namespace Windwalker\Query\Wrapper;

/**
 * The UuidBinaryWrapper class.
 */
class UuidBinWrapper extends UuidWrapper
{
    public function __invoke(mixed $src = null): mixed
    {
        return $this->uuid->getBytes();
    }

    public function __toString(): string
    {
        return $this->uuid->getBytes();
    }
}
