<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator\Rule;

use Windwalker\Validator\AbstractValidator;

/**
 * The NoneValidator class.
 *
 * @since  2.0
 */
class NoneValidator extends AbstractValidator
{
    /**
     * Test value and return boolean
     *
     * @param mixed $value
     *
     * @return  boolean
     */
    protected function test($value)
    {
        return true;
    }
}
