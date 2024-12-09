<?php

declare(strict_types=1);

namespace Windwalker\DOM;

use DOMDocument;
use DOMImplementation;
use DOMNode;
use Masterminds\HTML5;

/**
 * The HtmlFactory class.
 *
 * @method static DOMElement option(array $attrs = [], $content = null)
 * @method static DOMElement form(array $attrs = [], $content = null)
 * @method static DOMElement input(array $attrs = [], $content = null)
 * @method static DOMElement button(array $attrs = [], $content = null)
 *
 * @deprecated Use HTML5Factory instead.
 */
class HTMLFactory extends DOMFactory
{
    public const TEXT_SPAN = 1 << 0;

    public const TEXT_PARAGRAPH = 1 << 2;

    public const HTML_NODES = 1 << 3;

    /**
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  DOMElement
     */
    public static function element(string $name, $value = null): DOMElement
    {
        return parent::element($name, $value)->asHTML();
    }

    /**
     * @param  array  $options
     *
     * @return  DOMDocument
     * @throws \DOMException
     */
    public static function create(array $options = []): DOMDocument
    {
        $impl = new DOMImplementation();

        $dt = $impl->createDocumentType('html');

        $dom = $impl->createDocument('', '', $dt);
        $dom->registerNodeClass(\DOMElement::class, DOMElement::class);

        $dom->encoding = $options['encoding'] ?? 'UTF-8';

        return $dom;
    }

    public static function parse(string $text, int $options = self::TEXT_SPAN): ?DOMNode
    {
        if ($options & static::TEXT_SPAN) {
            $text = "<span>$text</span>";
        } else {
            $text = "<html>$text</html>";
        }

        $doc = static::create();
        $doc->loadHTML($text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        return $doc->documentElement->firstChild;
    }

    public static function saveHTML(DOMNode $node): string
    {
        if (class_exists(HTML5::class)) {
            /*
             * Native PHP DOMDocument will wrap `foo " bar` with single quote like: `attr='foo " bar'`
             * Some scanning software will consider it is a XSS vulnerabilities.
             * Use HTML5 package that can render it to correct `attr="foo &quote; bar"`
             */
            return DOMFactory::html5()->saveHTML($node);
        }

        return static::document()->saveHTML($node);
    }

    public static function __callStatic($name, $args): DOMElement
    {
        return DOMElement::create($name, ...$args);
    }
}
