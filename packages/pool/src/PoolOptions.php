<?php

declare(strict_types=1);

namespace Windwalker\Pool;

use Windwalker\Pool\Enum\Heartbeat;
use Windwalker\Utilities\Options\RecordOptions;

class PoolOptions extends RecordOptions
{
    public function __construct(
        public int $maxSize = 1,
        public int $minSize = 1,
        public int $maxWait = -1,
        public int $maxUses = -1,
        public int $maxLifetime = -1,
        public int $waitTimeout = -1,
        public int $idleTimeout = 60,
        public int $closeTimeout = 3,
        public int $leakTimeout = -1,
        public Heartbeat $heartbeat = Heartbeat::NONE,
        public int $timerInterval = -1,
    ) {
        $this->maxSize = max(1, $this->maxSize);
        $this->minSize = max(0, min($this->maxSize, $this->minSize));
    }
}
