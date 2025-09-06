<?php

declare(strict_types=1);

namespace Windwalker\Pool;

use Windwalker\Utilities\Options\RecordOptions;

class PoolOptions extends RecordOptions
{
    public function __construct(
        public int $maxSize = 1,
        public int $minSize = 1,
        public int $maxWait = -1,
        public int $waitTimeout = -1,
        public int $idleTimeout = 60,
        public int $closeTimeout = 3,
        public int $maxLifetime = -1,
        public int $maxUses = -1,
    ) {
        //
    }
}
