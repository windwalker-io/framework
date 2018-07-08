<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Form\Field;

use Windwalker\Dom\HtmlElement;

/**
 * The ButtonField class.
 *
 * @method  mixed|$this  text(string | integer $value = null)
 *
 * @since  2.1.8
 */
class ButtonField extends AbstractField
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected $type = 'button';

    /**
     * Property element.
     *
     * @var  string
     */
    protected $element = 'button';

    /**
     * prepareRenderInput
     *
     * @param array $attrs
     *
     * @return  array
     */
    public function prepare(&$attrs)
    {
        $attrs['type'] = $this->get('type', 'submit');
        $attrs['name'] = $this->getFieldName();
        $attrs['id'] = $this->getAttribute('id', $this->getId());
        $attrs['class'] = $this->getAttribute('class');
        $attrs['autofocus'] = $this->getAttribute('autofocus');
        $attrs['form'] = $this->getAttribute('form');
        $attrs['formaction'] = $this->getAttribute('formaction');
        $attrs['formenctype'] = $this->getAttribute('formenctype');
        $attrs['formmethod'] = $this->getAttribute('formmethod');
        $attrs['formnovalidate'] = $this->getAttribute('formnovalidate');
        $attrs['formtarget'] = $this->getAttribute('formtarget');
        $attrs['disabled'] = $this->getAttribute('disabled');
        $attrs['required'] = $this->required;
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
        return new HtmlElement($this->element, $this->getAttribute('text', $this->getValue()), $attrs);
    }
}
