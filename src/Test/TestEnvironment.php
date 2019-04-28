<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Test;

use Windwalker\Environment\PhpHelper;
use Windwalker\Environment\PlatformHelper;

/**
 * The TestEnvironment class.
 *
 * @since  2.0
 */
class TestEnvironment extends PlatformHelper
{
    /**
     * isCli
     *
     * @return  boolean
     */
    public static function isCli()
    {
        return PhpHelper::isCli();
    }
}
