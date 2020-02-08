<?php

/**
 * Part of ww4 project.
 *
 * @copyright  Copyright (C) 2019 __ORGANIZATION__.
 * @license    LGPL-2.0-or-later
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Assert;

use InvalidArgumentException;

/**
 * The ArgumentsAssert class.
 *
 * @since  3.5.17
 */
class ArgumentsAssert extends TypeAssert
{
    /**
     * @var  string
     */
    protected static $exceptionClass = InvalidArgumentException::class;
}
