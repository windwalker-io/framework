<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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
