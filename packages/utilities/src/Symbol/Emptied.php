<?php

declare(strict_types=1);


namespace Windwalker\Utilities\Symbol;

readonly final class Emptied
{
    public function __toString(): string
    {
        throw new \LogicException('Emptied symbol cannot be converted to string.');
    }
}
