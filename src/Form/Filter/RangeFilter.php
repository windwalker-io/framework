<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form\Filter;

/**
 * The RangeFilter class.
 *
 * @since  3.4
 */
class RangeFilter implements FilterInterface
{
    public const INT_UNSIGNED_MAX = 4294967295;

    public const INT_UNSIGNED_MIN = 0;

    public const INT_SIGNED_MAX = 2147483647;

    public const INT_SIGNED_MIN = -2147483648;

    public const BIGINT_UNSIGNED_MAX = '18446744073709551615';

    public const BIGINT_UNSIGNED_MIN = 0;

    public const BIGINT_SIGNED_MAX = '9223372036854775807';

    public const BIGINT_SIGNED_MIN = '-9223372036854775808';

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
        $this->min = $min;
        $this->max = $max;
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
