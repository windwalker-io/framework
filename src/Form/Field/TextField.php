<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Field;

use Windwalker\Dom\SimpleXml\XmlHelper;
use Windwalker\Html\Form\Datalist;
use Windwalker\Html\Option;

/**
 * The TextField class.
 *
 * @method  mixed|$this  placeholder(string $value = null)
 * @method  mixed|$this  size(integer $value = null)
 * @method  mixed|$this  maxlength(integer $value = null)
 * @method  mixed|$this  autofocus(string $value = null)
 * @method  mixed|$this  autocomplete(string $value = null)
 * @method  mixed|$this  onchange(string $value = null)
 * @method  mixed|$this  onfocus(string $value = null)
 * @method  mixed|$this  onblur(string $value = null)
 *
 * @since  2.0
 */
class TextField extends AbstractField
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected $type = 'text';

    /**
     * Property options.
     *
     * @var  Option[]
     */
    protected $options = [];

    /**
     * prepareRenderInput
     *
     * @param array $attrs
     *
     * @return  void
     */
    public function prepare(&$attrs)
    {
        $attrs['type']           = $this->type ?: 'text';
        $attrs['name']           = $this->getFieldName();
        $attrs['id']             = $this->getAttribute('id', $this->getId());
        $attrs['class']          = $this->getAttribute('class');
        $attrs['placeholder']    = $this->getAttribute('placeholder');
        $attrs['size']           = $this->getAttribute('size');
        $attrs['maxlength']      = $this->getAttribute('maxlength');
        $attrs['readonly']       = $this->getAttribute('readonly');
        $attrs['disabled']       = $this->getAttribute('disabled');
        $attrs['autofocus']      = $this->getAttribute('autofocus');
        $attrs['autocomplete']   = $this->getAttribute('autocomplete');
        $attrs['form']           = $this->getAttribute('form');
        $attrs['formenctype']    = $this->getAttribute('formenctype');
        $attrs['formmethod']     = $this->getAttribute('formmethod');
        $attrs['formnovalidate'] = $this->getAttribute('formnovalidate');
        $attrs['formtarget']     = $this->getAttribute('formtarget');
        $attrs['onchange']       = $this->getAttribute('onchange');
        $attrs['onfocus']        = $this->getAttribute('onfocus');
        $attrs['onblur']         = $this->getAttribute('onblur');
        $attrs['value']          = $this->escape($this->getValue());
        $attrs['list']           = $this->getAttribute('list', count($this->options) ? $this->getId() . '-list' : null);

        $attrs['required'] = $this->required;
    }

    /**
     * buildInput
     *
     * @param array $attrs
     *
     * @return  string
     *
     * @since  3.4
     */
    public function buildInput($attrs)
    {
        $html = parent::buildInput($attrs);

        if (count($this->options)) {
            $html .= new Datalist($attrs['list'], $this->options);
        }

        return $html;
    }

    /**
     * addOption
     *
     * @param Option $option
     *
     * @return  static
     */
    public function addOption(Option $option)
    {
        $options = [$option];

        $this->setOptions($options);

        return $this;
    }

    /**
     * option
     *
     * @param string $value
     * @param array  $attribs
     *
     * @return static
     */
    public function option($value = null, $attribs = [])
    {
        $this->addOption(new Option(null, $value, $attribs));

        return $this;
    }

    /**
     * setOptions
     *
     * @param array|Option[] $options
     *
     * @return  static
     */
    public function setOptions($options)
    {
        $this->handleOptions(null, $options);

        return $this;
    }

    /**
     * prepareOptions
     *
     * @param string|\SimpleXMLElement $xml
     * @param Option[]                 $options
     *
     * @throws \InvalidArgumentException
     * @return  void
     */
    protected function handleOptions($xml, $options = [])
    {
        if ($xml instanceof \SimpleXMLElement) {
            foreach ($xml->children() as $name => $option) {
                $attributes = XmlHelper::getAttributes($option);

                $option = new Option((string) $option, XmlHelper::getAttribute($option, 'value'), $attributes);

                $this->options[] = $option;
            }
        } else {
            foreach ($options as $name => $option) {
                if (!($option instanceof Option)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Please give me %s class as option, %s given.', 'Windwalker\\Html\\Option',
                            get_class($option)
                        )
                    );
                }

                if (is_numeric($name)) {
                    $this->options[] = $option;
                } else {
                    $this->options[$name] = $option;
                }
            }
        }
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
            'placeholder' => 'placeholder',
            'size' => 'size',
            'maxlength' => 'maxlength',
            'autofocus' => 'autofocus',
            'autocomplete' => 'autocomplete',
            'onchange' => 'onchange',
            'onfocus' => 'onfocus',
            'onblur' => 'onblur',
        ]
        );
    }
}
