<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Symbol;

final readonly class None
{
    public function __toString(): string
    {
        throw new \LogicException('None symbol cannot be converted to string.');
    }
}
