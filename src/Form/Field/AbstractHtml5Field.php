<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Form\Field;

/**
 * The AbstractHtml5Field class.
 *
 * @method  mixed|$this  max(integer $value = null)
 * @method  mixed|$this  min(integer $value = null)
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

        $attrs['max']    = $this->getAttribute('max');
        $attrs['min']    = $this->getAttribute('min');
        $attrs['step']   = $this->getAttribute('step');
        $attrs['patten'] = $this->getAttribute('pattern');
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
            parent::getAccessors(), [
            'max' => 'max',
            'min' => 'min',
            'step' => 'step',
            'patten' => 'patten',
        ]
        );
    }
}
