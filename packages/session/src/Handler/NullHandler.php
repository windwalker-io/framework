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
    public function destroy($id)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function gc($max_lifetime)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function write($id, $data)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function updateTimestamp($id, $data)
    {
        return true;
    }
}
