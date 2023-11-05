<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Injection;

use Windwalker\DI\Attributes\Decorator;

/**
 * The InnerStub class.
 */
#[Decorator(Wrapped::class)]
class InnerStub
{
}
