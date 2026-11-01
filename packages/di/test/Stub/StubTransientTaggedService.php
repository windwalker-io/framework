<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

use Windwalker\DI\Attributes\Service;

#[Service(tag: 'foo', shared: false)]
class StubTransientTaggedService {}
