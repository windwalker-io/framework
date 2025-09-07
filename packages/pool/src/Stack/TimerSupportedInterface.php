<?php

declare(strict_types=1);

namespace Windwalker\Pool\Stack;

use Windwalker\Pool\ConnectionInterface;

interface TimerSupportedInterface
{
    public function startTimer(\Closure $handler, int $intervalSeconds): void;

    public function stopTimer(): void;
}
