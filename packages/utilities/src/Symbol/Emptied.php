<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Symbol;

final readonly class Emptied
{
    public function __toString(): string
    {
        throw new \LogicException('Emptied symbol cannot be converted to string.');
    }
}
