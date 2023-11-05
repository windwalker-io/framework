<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Mock;

class WithEnum
{
    public function __construct(public FooEnum $foo)
    {
    }
}
