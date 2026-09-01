<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

interface TouchableStorageInterface
{
    public function updateExpiration(string $key, int $expiration = 0): bool;
}
