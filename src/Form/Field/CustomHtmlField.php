<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Form\Field;

/**
 * The ButtonField class.
 *
 * @method  mixed|$this  content(string|callable $value = null)
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
     * @return  void
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
            return $content($this, $attrs);
        }

        return $content;
    }

    /**
     * getAccessors
     *
     * @return  array
     */
    protected function getAccessors()
    {
        return array_merge(parent::getAccessors(), [
            'content',
        ]);
    }
}
