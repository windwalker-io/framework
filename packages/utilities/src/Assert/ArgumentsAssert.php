<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Assert;

use InvalidArgumentException;

/**
 * The ArgumentsAssert class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ArgumentsAssert extends TypeAssert
{
    protected static function exception(): callable
    {
        return static fn(string $msg) => new InvalidArgumentException($msg);
    }
}
