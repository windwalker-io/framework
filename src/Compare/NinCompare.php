<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Compare;

/**
 * Class NotinCompare
 *
 * @since 2.0
 */
class NinCompare extends InCompare
{
    /**
     * Operator symbol.
     *
     * @var  string
     */
    protected $operator = 'NOT IN';
}
