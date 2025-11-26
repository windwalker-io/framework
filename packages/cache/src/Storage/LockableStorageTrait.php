<?php

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

trait LockableStorageTrait
{
    public function locking(\Closure $handler, bool $enabled = true): mixed
    {
        if ($enabled) {
            $this->lock();
        }

        try {
            $result = $handler();
        } finally {
            if ($enabled) {
                $this->release();
            }
        }

        return $result;
    }
}
