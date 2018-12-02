<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Form\Field;

use Windwalker\Form\Filter\RangeFilter;

/**
 * The AbstractHtml5Field class.
 *
 * @method  mixed|$this  step(integer $value = null)
 * @method  mixed|$this  patten(string $value = null)
 *
 * @since  3.0.1
 */
class AbstractHtml5Field extends TextField
{
    /**
     * prepare
     *
     * @param array $attrs
     *
     * @return  void
     */
    public function prepare(&$attrs)
    {
        parent::prepare($attrs);

        $attrs['max'] = $this->getAttribute('max');
        $attrs['min'] = $this->getAttribute('min');
        $attrs['step'] = $this->getAttribute('step');
        $attrs['patten'] = $this->getAttribute('pattern');
    }

    /**
     * max
     *
     * @param int  $max
     * @param bool $addFilter
     * @param bool $forceInt
     *
     * @return  static|mixed
     *
     * @since  3.4.2
     */
    public function max($max = null, $addFilter = true, $forceInt = false)
    {
        if ($addFilter) {
            $this->addFilter(new RangeFilter(null, $max, $forceInt));
        }

        return $this->attr('max', $max);
    }

    /**
     * min
     *
     * @param int  $min
     * @param bool $addFilter
     * @param bool $forceInt
     *
     * @return  mixed|static
     *
     * @since  3.4.2
     */
    public function min($min = null, $addFilter = true, $forceInt = false)
    {
        if ($addFilter) {
            $this->addFilter(new RangeFilter($min, null, $forceInt));
        }

        return $this->attr('min', $min);
    }

    /**
     * range
     *
     * @param int  $min
     * @param int  $max
     * @param bool $addFilter
     * @param bool $forceInt
     *
     * @return  $this
     *
     * @since  3.4.2
     */
    public function range($min, $max, $addFilter = true, $forceInt = false)
    {
        if ($addFilter) {
            $this->addFilter(new RangeFilter($min, $max, $forceInt));
        }

        $this->min($min, false)
            ->max($max, false);

        return $this;
    }

    /**
     * getAccessors
     *
     * @return  array
     *
     * @since   3.1.2
     */
    protected function getAccessors()
    {
        return array_merge(
            parent::getAccessors(),
            [
                'max' => 'max',
                'min' => 'min',
                'step' => 'step',
                'patten' => 'patten',
            ]
        );
    }
}
