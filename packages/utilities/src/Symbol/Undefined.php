<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Symbol;

final readonly class Undefined
{
    public function __toString(): string
    {
        throw new \LogicException('Undefined symbol cannot be converted to string.');
    }
}
