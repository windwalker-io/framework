<?php
/**
 * Part of phoenix project.
 *
 * @copyright  Copyright (C) 2018 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Validator\Rule;

use Windwalker\Validator\AbstractValidator;

/**
 * The BetweenValidator class.
 *
 * @since  3.4.1
 */
class BetweenValidator extends AbstractValidator
{
    /**
     * Property start.
     *
     * @var float
     */
    protected $start;

    /**
     * Property end.
     *
     * @var float
     */
    protected $end;

    /**
     * BetweenValidator constructor.
     *
     * @param float $start
     * @param float $end
     */
    public function __construct($start, $end)
    {
        $this->start = (float) $start;
        $this->end   = (float) $end;
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
        return $value > $this->start && $value < $this->end;
    }

    /**
     * Method to get property Start
     *
     * @return  float
     *
     * @since  3.4.1
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Method to set property start
     *
     * @param   float $start
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.4.1
     */
    public function setStart($start)
    {
        $this->start = (float) $start;

        return $this;
    }

    /**
     * Method to get property End
     *
     * @return  float
     *
     * @since  3.4.1
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Method to set property end
     *
     * @param   float $end
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.4.1
     */
    public function setEnd($end)
    {
        $this->end = (float) $end;

        return $this;
    }
}
