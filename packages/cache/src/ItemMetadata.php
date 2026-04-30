<?php

declare(strict_types=1);

namespace Windwalker\Cache;

class ItemMetadata
{
    public function __construct(
        public int $expiry,
        public int $ctime,
    ) {
    }
}
