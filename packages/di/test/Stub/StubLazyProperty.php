<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

use Windwalker\DI\Attributes\Inject;
use Windwalker\DI\Attributes\Lazy;
use Windwalker\DI\Test\Mock\Bar;

class StubLazyProperty
{
    #[Inject, Lazy]
    public Bar $lazy;
}
