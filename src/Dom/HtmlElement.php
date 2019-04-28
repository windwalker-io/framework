<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Dom;

use Windwalker\Dom\Builder\HtmlBuilder;
use Windwalker\String\Str;

/**
 * The Html element object.
 *
 * @property-read DOMTokenList $classList
 * @property-read DOMStringMap $dataset
 *
 * @since 2.0
 */
class HtmlElement extends DomElement
{
    /**
     * toString
     *
     * @param boolean $forcePair
     *
     * @return  string
     */
    public function toString($forcePair = false)
    {
        return HtmlBuilder::create($this->name, $this->content, $this->attribs, $forcePair);
    }

    /**
     * addClass
     *
     * @param string|callable $class
     *
     * @return  static
     *
     * @since  3.5.3
     */
    public function addClass($class): self
    {
        $class = Str::toString($class);

        $classes = array_filter(explode(' ', $class), 'strlen');

        $this->classList->add(...$classes);

        return $this;
    }

    /**
     * removeClass
     *
     * @param string|callable $class
     *
     * @return  static
     *
     * @since  3.5.3
     */
    public function removeClass($class): self
    {
        $class = Str::toString($class);

        $classes = array_filter(explode(' ', $class), 'strlen');

        $this->classList->remove(...$classes);

        return $this;
    }

    /**
     * toggleClass
     *
     * @param string    $class
     * @param bool|null $force
     *
     * @return  static
     *
     * @since  3.5.3
     */
    public function toggleClass(string $class, ?bool $force = null): self
    {
        $this->classList->toggle($class, $force);

        return $this;
    }

    /**
     * hasClass
     *
     * @param string $class
     *
     * @return  static
     *
     * @since  3.5.3
     */
    public function hasClass(string $class): self
    {
        $this->classList->contains($class);

        return $this;
    }

    /**
     * data
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return  string|static
     *
     * @since  3.5.3
     */
    public function data(string $name, $value = null)
    {
        if ($value === null) {
            return $this->getAttribute('data-' . $name);
        }

        return $this->setAttribute('data-' . $name, $value);
    }

    /**
     * __get
     *
     * @param string $name
     *
     * @return  mixed
     *
     * @since  3.5.3
     */
    public function __get($name)
    {
        if ($name === 'classList') {
            return new DOMTokenList($this);
        }

        if ($name === 'dataset') {
            return new DOMStringMap($this);
        }

        return $this->$name;
    }
}
