<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Mock;

class WithVariadic
{
    public array $args;

    /**
     * WithVariadic constructor.
     */
    public function __construct(...$args)
    {
        $this->args = $args;
    }
}
