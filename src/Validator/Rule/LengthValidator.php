<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2018 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator\Rule;

use Windwalker\Validator\AbstractValidator;

/**
 * The LengthValidator class.
 *
 * @since  3.4
 */
class LengthValidator extends AbstractValidator
{
    /**
     * Property min.
     *
     * @var  int
     */
    protected $min;

    /**
     * Property max.
     *
     * @var  int
     */
    protected $max;

    /**
     * Property utf8.
     *
     * @var  bool
     */
    protected $utf8;

    /**
     * LengthValidator constructor.
     *
     * @param int  $min
     * @param int  $max
     * @param bool $utf8
     */
    public function __construct($min = 0, $max = null, $utf8 = true)
    {
        $this->min = (int) $min;

        if ($this->min < 0) {
            throw new \InvalidArgumentException('$min should not less than 0.');
        }

        $this->max = $max;

        if (!is_int($this->max) && $this->max !== null) {
            throw new \InvalidArgumentException('$max should be int or NULL.');
        }

        $this->utf8 = (bool) $utf8;
    }

    /**
     * Test value and return boolean
     *
     * @param mixed $value
     *
     * @return  boolean
     */
    protected function test($value)
    {
        $len = $this->utf8 ? mb_strlen($value) : strlen($value);

        if ($len < $this->min) {
            return false;
        }

        if ($this->max !== null && $len > $this->max) {
            return false;
        }

        return true;
    }
}
