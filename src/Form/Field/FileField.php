<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form\Field;

/**
 * The TextField class.
 *
 * @method  mixed|$this  multiple(bool $value = null)
 * @method  mixed|$this  accept(bool $value = null)
 * @method  mixed|$this  capture(bool $value = null)
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
        $attrs['accept'] = $this->getAttribute('accept');
        $attrs['capture'] = $this->getAttribute('capture');
    }

    /**
     * buildInput
     *
     * @param array $attrs
     *
     * @return  string
     *
     * @since  3.5.8
     */
    public function buildInput($attrs)
    {
        if ($attrs['multiple']) {
            $attrs['name'] .= '[]';
        }

        return parent::buildInput($attrs);
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
                'multiple' => 'multiple',
                'accept' => 'accept',
                'capture' => 'capture',
            ]
        );
    }
}
