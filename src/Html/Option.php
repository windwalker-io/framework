<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Html;

use Windwalker\Dom\HtmlElement;

/**
 * The HtmlOption class.
 *
 * @since  2.0
 */
class Option extends HtmlElement
{
    /**
     * Property value.
     *
     * @var  string
     */
    protected $value = '';

    /**
     * Property attributes.
     *
     * @var  string
     */
    protected $attributes = [];

    /**
     * @param string $text
     * @param string $value
     * @param array  $attribs
     */
    public function __construct($text = null, $value = null, $attribs = [])
    {
        $this->value = $value;

        $attribs['value'] = $value;

        parent::__construct('option', $text, $attribs);
    }

    /**
     * Method to get property Value
     *
     * @return  string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Method to set property value
     *
     * @param   string $value
     *
     * @return  static  Return self to support chaining.
     */
    public function setValue($value)
    {
        $this->value = $value;

        $this['value'] = $value;

        return $this;
    }
}
