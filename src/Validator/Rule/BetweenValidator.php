<?php
/**
 * Part of phoenix project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Validator\Rule;

use Windwalker\Validator\AbstractValidator;

/**
 * The BetweenValidator class.
 *
 * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
     */
    public function setEnd($end)
    {
        $this->end = (float) $end;

        return $this;
    }
}
