<?php

declare(strict_types=1);

namespace Windwalker\DOM;

use Dom\Node;

use function Windwalker\value;

if (!function_exists('\Windwalker\DOM\parse_html')) {
    function parse_html(string $html, int $options = HTMLFactory::TEXT_SPAN): ?Node
    {
        return HTML5Factory::parse($html, $options);
    }
}

if (!function_exists('\Windwalker\DOM\html')) {
    #[\Deprecated('No replace.')]
    function html(DOMElement $element): DOMElement
    {
        return $element->asHTML();
    }
}

if (!function_exists('\Windwalker\DOM\xml')) {
    #[\Deprecated('No replace.')]
    function xml(DOMElement $element): DOMElement
    {
        return $element->asXML();
    }
}

if (!function_exists('\Windwalker\DOM\h')) {
    function h(string $name, array|\Closure $attributes = [], mixed $content = null): HTMLElement
    {
        return HTML5Factory::element($name, $attributes, $content);
    }
}

if (!function_exists('\Windwalker\DOM\div')) {
    function div(array|\Closure $attributes = [], $content = null): HTMLElement
    {
        return h('div', $attributes, $content);
    }
}

if (!function_exists('\Windwalker\DOM\span')) {
    function span(array|\Closure $attributes = [], $content = null): HTMLElement
    {
        return h('span', $attributes, $content);
    }
}

if (!function_exists('\Windwalker\DOM\img')) {
    function img(mixed $src, array|\Closure $attributes = []): HTMLElement
    {
        $attributes['src'] = value($src);

        return h('img', $attributes);
    }
}
