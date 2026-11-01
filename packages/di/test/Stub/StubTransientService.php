<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

use Windwalker\DI\Attributes\Service;

#[Service(shared: false)]
class StubTransientService {}
