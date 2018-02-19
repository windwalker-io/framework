<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Field;

use Windwalker\Dom\SimpleXml\XmlHelper;
use Windwalker\Html\Option;
use Windwalker\Html\Select\SelectList;

/**
 * The ListField class.
 *
 * @method  mixed|$this  size(integer $value = null)
 * @method  mixed|$this  onchange(string $value = null)
 * @method  mixed|$this  multiple(bool $value = null)
 *
 * @since  2.0
 */
class ListField extends AbstractField
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected $type = 'list';

    /**
     * Property options.
     *
     * @var  Option[]
     */
    protected $options = [];

    /**
     * Property currentGroup.
     *
     * @var  string
     */
    protected $currentGroup;

    /**
     * @param string $name
     * @param null   $label
     * @param array  $options
     * @param array  $attributes
     * @param null   $filter
     * @param null   $rule
     */
    public function __construct(
        $name = null,
        $label = null,
        $options = [],
        $attributes = [],
        $filter = null,
        $rule = null
    ) {
        parent::__construct($name, $label, $attributes, $filter, $rule);

        $this->handleOptions($name, $options);
    }

    /**
     * prepareRenderInput
     *
     * @param array $attrs
     *
     * @return  array
     */
    public function prepare(&$attrs)
    {
        $attrs['name']     = $this->getFieldName();
        $attrs['id']       = $this->getAttribute('id', $this->getId());
        $attrs['class']    = $this->getAttribute('class');
        $attrs['size']     = $this->getAttribute('size');
        $attrs['readonly'] = $this->getAttribute('readonly');
        $attrs['disabled'] = $this->getAttribute('disabled');
        $attrs['onchange'] = $this->getAttribute('onchange');
        $attrs['multiple'] = $this->getAttribute('multiple');
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
        return array_merge(parent::getAccessors(), [
                'size' => 'size',
                'onchange' => 'onchange',
                'multiple' => 'multiple',
            ]
        );
    }

    /**
     * buildInput
     *
     * @param array $attrs
     *
     * @return  mixed|void
     */
    public function buildInput($attrs)
    {
        $options = $this->getOptions();

        return new SelectList($this->getFieldName(), $options, $attrs, $this->getValue(), $this->getBool('multiple'));
    }

    /**
     * getOptions
     *
     * @return  array|Option[]
     */
    public function getOptions()
    {
        return array_merge($this->options, $this->prepareOptions());
    }

    /**
     * setOptions
     *
     * @param array|Option[] $options
     *
     * @return  static
     */
    public function setOptions($options, $group = null)
    {
        if ($group) {
            $options = [$group => $options];
        }

        $this->handleOptions(null, $options);

        return $this;
    }

    /**
     * addOption
     *
     * @param Option $option
     * @param string $group
     *
     * @return  static
     */
    public function addOption(Option $option, $group = null)
    {
        $options = [$option];

        if ($group === null) {
            $group = $this->currentGroup;
        }

        $this->setOptions($options, $group);

        return $this;
    }

    /**
     * option
     *
     * @param string $text
     * @param string $value
     * @param array  $attribs
     * @param string $group
     *
     * @return static
     */
    public function option($text = null, $value = null, $attribs = [], $group = null)
    {
        $this->addOption(new Option($text, $value, $attribs), $group);

        return $this;
    }

    /**
     * optionGroup
     *
     * @param string   $name
     * @param \Closure $callback
     *
     * @return  static
     */
    public function group($name, \Closure $callback)
    {
        $this->currentGroup = $name;

        $callback($this);

        $this->currentGroup = null;

        return $this;
    }

    /**
     * prepareOptions
     *
     * @return  array|Option[]
     */
    protected function prepareOptions()
    {
        return [];
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
                if ($option->getName() === 'optgroup') {
                    foreach ($option->children() as $opt) {
                        $attributes = XmlHelper::getAttributes($opt);

                        $opt = new Option((string)$opt, XmlHelper::getAttribute($opt, 'value'), $attributes);

                        $this->options[XmlHelper::getAttribute($option, 'label')][] = $opt;
                    }
                } else {
                    $attributes = XmlHelper::getAttributes($option);

                    $option = new Option((string)$option, XmlHelper::getAttribute($option, 'value'), $attributes);

                    $this->options[] = $option;
                }
            }
        } else {
            foreach ($options as $name => $option) {
                // If is array, means it is group
                if (is_array($option)) {
                    foreach ($option as $opt) {
                        if (!($opt instanceof Option)) {
                            throw new \InvalidArgumentException(
                                sprintf('Please give me %s class as option, %s given.', 'Windwalker\\Html\\Option',
                                    get_class($opt))
                            );
                        }
                    }
                } // If not array, means it is option
                else {
                    if (!($option instanceof Option)) {
                        throw new \InvalidArgumentException(
                            sprintf('Please give me %s class as option, %s given.', 'Windwalker\\Html\\Option',
                                get_class($option))
                        );
                    }
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
     * getValue
     *
     * @return  array
     */
    public function getValue()
    {
        $value = parent::getValue();

        if ($this->getBool('multiple') && is_string($value)) {
            $value = explode(',', $value);
        }

        return $value;
    }
}
