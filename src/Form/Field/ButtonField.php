<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Form\Field;

use Windwalker\Dom\HtmlElement;

/**
 * The ButtonField class.
 *
 * @method  mixed|$this  text(string | integer $value = null)
 * @method  mixed|$this  buttonType(string | integer $value = null)
 * @method  mixed|$this  element(string $value = null)
 *
 * @since  2.1.8
 */
class ButtonField extends AbstractField
{
    public const ELEMENT_BUTTON = 'button';
    public const ELEMENT_LINK = 'link';

    public const TYPE_BUTTON = 'button';
    public const TYPE_CLEAR = 'clear';
    public const TYPE_SUBMIT = 'submit';

    /**
     * Property type.
     *
     * @var  string
     */
    protected $type = 'button';

    /**
     * prepareRenderInput
     *
     * @param array $attrs
     *
     * @return void
     */
    public function prepare(&$attrs)
    {
        $attrs['name']           = $this->getFieldName();
        $attrs['id']             = $this->getAttribute('id', $this->getId());
        $attrs['class']          = $this->getAttribute('class');
        $attrs['autofocus']      = $this->getAttribute('autofocus');
        $attrs['form']           = $this->getAttribute('form');
        $attrs['formaction']     = $this->getAttribute('formaction');
        $attrs['formenctype']    = $this->getAttribute('formenctype');
        $attrs['formmethod']     = $this->getAttribute('formmethod');
        $attrs['formnovalidate'] = $this->getAttribute('formnovalidate');
        $attrs['formtarget']     = $this->getAttribute('formtarget');
        $attrs['disabled']       = $this->getAttribute('disabled');
        $attrs['required']       = $this->required;
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
                'text' => 'text',
                'buttonType' => 'type',
                'element' => 'element',
            ]
        );
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
        if ($this->element() === static::ELEMENT_BUTTON) {
            $attrs['type'] = $this->buttonType() ?: 'submit';
        }

        return new HtmlElement(
            $this->element(),
            $this->getAttribute('text', $this->getValue()),
            $attrs
        );
    }
}
