<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Form\Field;

/**
 * The ButtonField class.
 *
 * @since  2.1.8
 */
class CustomHtmlField extends AbstractField
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected $type = 'html';

    /**
     * prepareRenderInput
     *
     * @param array $attrs
     *
     * @return  array
     */
    public function prepare(&$attrs)
    {
    }

    /**
     * buildInput
     *
     * @param array $attrs
     *
     * @return  mixed
     */
    public function buildInput($attrs)
    {
        $content = $this->getAttribute('content');

        if (is_callable($content)) {
            return call_user_func($content, $this);
        }

        return $content;
    }

    /**
     * content
     *
     * @param   string $content
     *
     * @return  static
     */
    public function content($content)
    {
        $this->set('content', $content);

        return $this;
    }
}
