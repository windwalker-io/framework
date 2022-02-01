<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Session\Handler;

/**
 * The NullHandler class.
 */
class NullHandler extends AbstractHandler
{
    protected function doRead(string $id): ?string
    {
        return null;
    }

    public static function isSupported(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $id): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function gc($max_lifetime): int|false
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function write(string $id, string $data): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function updateTimestamp(string $id, string $data): bool
    {
        return true;
    }
}
