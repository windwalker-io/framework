<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Form\Filter;

/**
 * The RangeFilter class.
 *
 * @since  __DEPLOY_VERSION__
 */
class RangeFilter implements FilterInterface
{
    /**
     * Property min.
     *
     * @var float|int
     */
    protected $min;

    /**
     * Property max.
     *
     * @var float|int
     */
    protected $max;

    /**
     * Property forceInt.
     *
     * @var bool
     */
    protected $forceInt;

    /**
     * RangeFilter constructor.
     *
     * @param float $min
     * @param float $max
     * @param bool  $forceInt
     */
    public function __construct($min = null, $max = null, $forceInt = false)
    {
        $this->min      = $min;
        $this->max      = $max;
        $this->forceInt = (bool) $forceInt;
    }

    /**
     * clean
     *
     * @param string|int|float $num
     *
     * @return  mixed
     */
    public function clean($num)
    {
        $num = (float) $num;

        if ($this->min !== null && $num < $this->min) {
            $num = (float) $this->min;
        }

        if ($this->max !== null && $num > $this->max) {
            $num = (float) $this->max;
        }

        if ($this->forceInt) {
            $num = (int) $num;
        }

        return $num;
    }
}
