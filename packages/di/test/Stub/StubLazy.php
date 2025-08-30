<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

use Windwalker\DI\Attributes\Lazy;

#[Lazy]
class StubLazy
{
    public $a = 1234;

    public function a()
    {
        return $this->a;
    }
}
