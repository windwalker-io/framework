<?php

declare(strict_types=1);

namespace Windwalker\Query\Wrapper;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Windwalker\Utilities\Wrapper\WrapperInterface;

/**
 * The UuidBinaryWrapper class.
 */
class UuidBinWrapper extends UuidWrapper
{
    public function __invoke(mixed $src = null): mixed
    {
        return $this->uuid->getBytes();
    }

    public function __toString()
    {
        return $this->uuid->getBytes();
    }
}
