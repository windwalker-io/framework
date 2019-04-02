<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form\Test\Stub;

use Windwalker\Validator\AbstractValidator;

/**
 * The StubValidator class.
 *
 * @since  2.0
 */
class StubValidator extends AbstractValidator
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
        $this->setError('Test Fail: ' . $value);

        return false;
    }
}
