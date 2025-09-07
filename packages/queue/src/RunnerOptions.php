<?php

declare(strict_types=1);

namespace Windwalker\Queue;

class RunnerOptions
{
    public function __construct(
        public bool $once = false,
        public int $backoff = 0,
        public bool $force = false,
        public int $memoryLimit = 128,
        public int|float|string $sleep = 1.0,
        public int $tries = 5,
        public int $timeout = 60,
        public int $maxRuns = 0,
        public int $lifetime = 0,
        public bool $stopWhenEmpty = false,
        public ?string $restartSignal = null,
        public ?\Closure $controllerFactory = null,
    ) {
    }
}
