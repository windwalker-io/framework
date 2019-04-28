<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\IO\Cli\Color;

/**
 * The NullColorProcessor class.
 *
 * @since  2.0
 */
class NoColorProcessor extends ColorProcessor
{
    /**
     * Flag to remove color codes from the output
     *
     * @var    boolean
     * @since  2.0
     */
    public $noColors = true;
}
