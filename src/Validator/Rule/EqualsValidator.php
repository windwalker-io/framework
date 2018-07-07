<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Validator\Rule;

use Windwalker\Validator\AbstractValidator;

/**
 * The EqualsValidator class.
 *
 * @since  2.0
 */
class EqualsValidator extends AbstractValidator
{
    /**
     * Property data.
     *
     * @var  mixed
     */
    protected $compare = '';

    /**
     * Property strict.
     *
     * @var  boolean
     */
    protected $strict = false;

    /**
     * Class init.
     *
     * @param mixed $compare
     * @param bool  $strict
     */
    public function __construct($compare, $strict = false)
    {
        $this->compare = $compare;
        $this->strict  = $strict;
    }

    /**
     * Test this value.
     *
     * @param mixed $value
     *
     * @return  boolean
     */
    protected function test($value)
    {
        if ($this->strict) {
            return ($this->compare === $value);
        }

        return ($this->compare == $value);
    }
}
