<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DOM;

use DOMNode;

use function Windwalker\value;

if (!function_exists('\Windwalker\DOM\parse_html')) {
    function parse_html(string $html, int $options = HTMLFactory::TEXT_SPAN): DOMNode
    {
        return HTMLFactory::parse($html, $options);
    }
}

if (!function_exists('\Windwalker\DOM\html')) {
    /**
     * html
     *
     * @param  DOMElement  $element
     *
     * @return  DOMElement
     */
    function html(DOMElement $element): DOMElement
    {
        return $element->asHTML();
    }
}

if (!function_exists('\Windwalker\DOM\xml')) {
    /**
     * xml
     *
     * @param  DOMElement  $element
     *
     * @return  DOMElement
     */
    function xml(DOMElement $element): DOMElement
    {
        return $element->asXML();
    }
}

if (!function_exists('\Windwalker\DOM\h')) {
    /**
     * h
     *
     * @param  string  $name
     * @param  array   $attributes
     * @param  mixed   $content
     *
     * @return  DOMElement
     */
    function h(string $name, array $attributes = [], $content = null): DOMElement
    {
        return HTMLElement::create($name, $attributes, $content);
    }
}

if (!function_exists('\Windwalker\DOM\div')) {
    /**
     * div
     *
     * @param  array  $attributes
     * @param  mixed  $content
     *
     * @return  DOMElement
     */
    function div(array $attributes = [], $content = null): DOMElement
    {
        return h('div', $attributes, $content);
    }
}

if (!function_exists('\Windwalker\DOM\span')) {
    /**
     * span
     *
     * @param  array  $attributes
     * @param  mixed  $content
     *
     * @return  DOMElement
     */
    function span(array $attributes = [], $content = null): DOMElement
    {
        return h('span', $attributes, $content);
    }
}

if (!function_exists('\Windwalker\DOM\img')) {
    /**
     * span
     *
     * @param  mixed  $src
     * @param  array  $attributes
     *
     * @return  DOMElement
     */
    function img(mixed $src, array $attributes = []): DOMElement
    {
        $attributes['src'] = value($src);

        return h('img', $attributes);
    }
}
