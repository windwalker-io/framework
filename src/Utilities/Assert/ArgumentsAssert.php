<?php

/**
 * Part of ww4 project.
 *
 * @copyright  Copyright (C) 2019 __ORGANIZATION__.
 * @license    __LICENSE__
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
    /**
     * @var  string
     */
    protected static $exceptionClass = InvalidArgumentException::class;
}
