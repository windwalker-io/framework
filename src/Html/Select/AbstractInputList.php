<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Html\Select;

use Windwalker\Dom\Builder\HtmlBuilder;
use Windwalker\Dom\HtmlElement;
use Windwalker\Dom\HtmlElements;
use Windwalker\Html\Option;

/**
 * The InputList class.
 *
 * @since  2.0
 */
class AbstractInputList extends HtmlElement
{
    /**
     * Property selected.
     *
     * @var  mixed
     */
    protected $checked = null;

    /**
     * Property disabled.
     *
     * @var  boolean
     */
    protected $disabled = false;

    /**
     * Property readonly.
     *
     * @var  boolean
     */
    protected $readonly = false;

    /**
     * Element content.
     *
     * @var  Option[]
     */
    protected $content = [];

    /**
     * Property type.
     *
     * @var  string
     */
    protected $type = '';

    /**
     * Constructor
     *
     * @param string     $name
     * @param mixed|null $options
     * @param array      $attribs
     * @param mixed      $checked
     */
    public function __construct($name, $options = [], $attribs = [], $checked = null)
    {
        $attribs['name'] = $name;

        $this->checked = $checked;

        parent::__construct('span', (array) $options, $attribs);
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
        if ($group) {
            $content = $this->content[$group];

            array_push($content, $option);

            $this->content[$group] = $content;
        } else {
            $this->content[] = $option;
        }

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
     * @return  SelectList
     */
    public function option($text = null, $value = null, $attribs = [], $group = null)
    {
        return $this->addOption(new Option($text, $value, $attribs), $group);
    }

    /**
     * prepareOptions
     *
     * @return  void
     */
    public function prepareOptions()
    {
        foreach ($this->content as $key => $option) {
            if (!($option instanceof Option)) {
                throw new \InvalidArgumentException('List item should be an Windwalker\\Html\\Option object.');
            }

            if ($this->isChecked($option)) {
                $option['checked'] = 'checked';
            }

            $attrs = $option->getAttributes();

            $attrs['type'] = $this->type;
            $attrs['name'] = $this->getAttribute('name');

            $attrs['id'] = $option->getAttribute('id');
            $attrs['id'] = $attrs['id'] ?: strtolower(
                trim(
                    preg_replace(
                        '/[^A-Z0-9_\.-]/i',
                        '-',
                        $attrs['name'] ?: 'empty'
                    ),
                    '-'
                )
            );
            $attrs['id'] .= '-' . strtolower(
                trim(
                    preg_replace(
                        '/[^A-Z0-9_\.-]/i',
                        '-',
                        (string) ($option->getValue() ?: 'empty')
                    ),
                    '-'
                )
            );
            $attrs['id'] = 'input-' . $attrs['id'];
            $attrs['disabled'] = $this->disabled;
            $attrs['readonly'] = $this->readonly;

            // Do not affect source options
            $option = clone $option;

            $option->setAttributes($attrs);

            $input = new HtmlElement('input', null, $attrs);

            $option->setAttribute('disabled', null);
            $option->setAttribute('readonly', null);

            $label = $this->createLabel($option);

            $this->content[$key] = new HtmlElements([$input, $label]);
        }
    }

    /**
     * isChecked
     *
     * @param  Option $option
     *
     * @return  bool
     */
    protected function isChecked(Option $option)
    {
        return (string) $option->getValue() === (string) $this->getChecked();
    }

    /**
     * toString
     *
     * @param boolean $forcePair
     *
     * @return  string
     */
    public function toString($forcePair = false)
    {
        if ($this->getAttribute('disabled')) {
            $this->disabled = true;
            $this->setAttribute('disabled', null);
        }

        if ($this->getAttribute('readonly')) {
            $this->readonly = true;
            $this->setAttribute('readonly', null);
        }

        $this->prepareOptions();

        $attrs = $this->getAttributes();
        $attrs['id'] = $this->getAttribute('id');
        $attrs['class'] = $this->type . '-inputs ' . $this->getAttribute('class');

        $attrs['name'] = null;
        $attrs['onchange'] = null;
        $attrs['size'] = null;

        return HtmlBuilder::create($this->name, $this->content, $attrs, $forcePair);
    }

    /**
     * createLabel
     *
     * @param Option $option
     *
     * @return  Htmlelement
     */
    protected function createLabel($option)
    {
        $attrs = $option->getAttributes();

        $attrs['id'] = $option->getAttribute('id') . '-label';
        $attrs['for'] = $option->getAttribute('id');
        $attrs['value'] = null;
        $attrs['checked'] = null;
        $attrs['type'] = null;
        $attrs['name'] = null;

        return new HtmlElement('label', $option->getContent(), $attrs);
    }

    /**
     * Method to get property Checked
     *
     * @return  mixed
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * Method to set property checked
     *
     * @param   mixed $checked
     *
     * @return  static  Return self to support chaining.
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * Method to get property Disabled
     *
     * @return  boolean
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * Method to set property disabled
     *
     * @param   boolean $disabled
     *
     * @return  static  Return self to support chaining.
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Method to get property Readonly
     *
     * @return  boolean
     */
    public function getReadonly()
    {
        return $this->readonly;
    }

    /**
     * Method to set property readonly
     *
     * @param   boolean $readonly
     *
     * @return  static  Return self to support chaining.
     */
    public function setReadonly($readonly)
    {
        $this->readonly = $readonly;

        return $this;
    }
}
