<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Field;

/**
 * The TextField class.
 *
 * @method  mixed|$this  multiple(bool $value = null)
 *
 * @since  2.0
 */
class FileField extends TextField
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected $type = 'file';

    /**
     * prepareRenderInput
     *
     * @param array $attrs
     *
     * @return  array
     */
    public function prepare(&$attrs)
    {
        parent::prepare($attrs);

        $attrs['multiple'] = $this->getAttribute('multiple');
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
        return array_merge(parent::getAccessors(), [
                'multiple' => 'multiple',
            ]
        );
    }
}
