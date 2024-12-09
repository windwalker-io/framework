<?php

declare(strict_types=1);

namespace Windwalker\DOM;

use Dom\Attr;
use Dom\Comment;
use Dom\DocumentFragment;
use Dom\Element;
use Dom\HTMLDocument;
use Dom\HTMLElement as NativeHTMLElement;
use Dom\Node;
use Dom\NodeList;
use Dom\Text;
use JetBrains\PhpStorm\ArrayShape;
use Masterminds\HTML5;
use Windwalker\DOM\HTML5\OutputRules;
use Windwalker\DOM\HTML5\Traverser;
use Windwalker\Utilities\Str;

use function Windwalker\value;

/**
 * The HtmlFactory class.
 *
 * @method static HTMLElement option(array $attrs = [], $content = null)
 * @method static HTMLElement form(array $attrs = [], $content = null)
 * @method static HTMLElement input(array $attrs = [], $content = null)
 * @method static HTMLElement button(array $attrs = [], $content = null)
 */
class HTML5Factory
{
    public const TEXT_SPAN = 1 << 0;

    public const TEXT_PARAGRAPH = 1 << 2;

    public const HTML_NODES = 1 << 3;

    protected static ?HTMLDocument $dom = null;

    protected static HTML5 $html5;

    public static function document(?HTMLDocument $dom = null): HTMLDocument
    {
        if ($dom) {
            static::$dom = $dom;
        }

        if (!static::$dom) {
            static::$dom = static::create();
        }

        return static::$dom;
    }

    /**
     * @return  DocumentFragment
     */
    public static function fragment(): DocumentFragment
    {
        return static::document()->createDocumentFragment();
    }

    /**
     * @param  string  $name
     * @param  array   $attributes
     * @param  mixed   $value
     *
     * @return  HTMLElement
     */
    public static function element(string $name, array $attributes = [], mixed $value = null): HTMLElement
    {
        [$name, $id, $class] = array_values(static::splitCSSSelector($name));

        $ele = static::document()->createElement($name);

        if ($id !== null) {
            $attributes['id'] = $id;
        }

        $ele->setAttributes($attributes);

        if ($class !== null) {
            $ele->addClass($class);
        }

        if ($value !== null) {
            static::insertContentTo($value, $ele);
        }

        return $ele;
    }

    /**
     * @param  string  $data
     *
     * @return  Comment
     */
    public static function comment(string $data): Comment
    {
        return static::document()->createComment($data);
    }

    /**
     * textNode
     *
     * @param  string  $content
     *
     * @return  Text
     */
    public static function textNode(string $content): Text
    {
        return static::document()->createTextNode($content);
    }

    /**
     * create
     *
     * @param  array  $options
     *
     * @return  HTMLDocument
     */
    public static function create(array $options = []): HTMLDocument
    {
        $encoding = $options['encoding'] ?? 'UTF-8';

        /** @var HTMLDocument $dom */
        $dom = HTMLDocument::createEmpty();
        $dom->registerNodeClass(NativeHTMLElement::class, HTMLElement::class);

        return $dom;
    }

    public static function parse(string $text, int $options = 0, ?string $overrideEncoding = null): ?Node
    {
        /** @var HTMLDocument $doc */
        $doc = HTMLDocument::createFromString($text, $options, $overrideEncoding);

        return $doc->documentElement->firstChild;
    }

    public static function reset(): void
    {
        static::$dom = null;
    }

    public static function html5(?HTML5 $html5 = null): HTML5
    {
        if ($html5) {
            static::$html5 = $html5;
        }

        if (!isset(static::$html5)) {
            static::$html5 = new HTML5(
                [
                    'disable_html_ns' => true,
                    'target_document' => static::document(),
                ],
            );
        }

        return static::$html5;
    }

    public static function saveHtml(Node $node): string
    {
        if (class_exists(HTML5::class)) {
            /*
             * Native PHP DOMDocument will wrap `foo " bar` with single quote like: `attr='foo " bar'`
             * Some scanning software will consider it is a XSS vulnerabilities.
             * Use HTML5 package that can render it to correct `attr="foo &quote; bar"`
             */
            return static::saveHTML5($node);
        }

        if ($node instanceof HTMLDocument) {
            return $node->saveHtml($node);
        }

        return static::document()->saveHtml($node);
    }

    #[ArrayShape(['name' => "mixed|string", 'id' => "null|string", 'class' => "null|string"])]
    public static function splitCSSSelector(
        string $name,
    ): array {
        $tokens = preg_split(
            '/([\.#]?[^\s#.]+)/',
            $name,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY,
        );

        if ($tokens === []) {
            throw new \InvalidArgumentException('Tag name is empty');
        }

        if (!in_array($tokens[0][0] ?? '', ['#', '.'], true)) {
            $name = array_shift($tokens);
        } else {
            $name = 'div';
        }

        $id = null;
        $class = [];

        foreach ($tokens as $token) {
            if (str_starts_with($token, '#')) {
                $id = ltrim($token, '#');
            } else {
                $class[] = ltrim($token, '.');
            }
        }

        return [
            'name' => $name,
            'id' => $id,
            'class' => $class ? implode(' ', $class) : null,
        ];
    }

    protected static function valueToString(mixed $value): string
    {
        $value = value($value);

        if (is_stringable($value)) {
            return (string) $value;
        }

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_THROW_ON_ERROR);
        }

        return $value;
    }

    protected static function insertContentTo(mixed $content, Node $node): Node
    {
        $content = value($content);

        if (is_array($content) || $content instanceof NodeList) {
            /** @var DocumentFragment $fragment */
            $fragment = $node->ownerDocument->createDocumentFragment();

            foreach ($content as $key => $c) {
                static::insertContentTo($c, $fragment);
            }

            if (count($fragment->childNodes) === 0) {
                return $node;
            }

            return $node->appendChild($fragment);
        }

        if ($content instanceof Node) {
            return $node->appendChild($content);
        }

        $text = $node->ownerDocument->createTextNode((string) $content);

        return $node->appendChild($text);
    }

    public static function buildAttributes(array|Element $attributes): string
    {
        if ($attributes instanceof Element) {
            $attributes = array_map(
                static fn(Attr $attr) => $attr->value,
                iterator_to_array($attributes->attributes),
            );
        }

        $ele = static::element('root', $attributes, '')->render();

        return trim(Str::removeLeft(Str::removeRight($ele, '></root>', 'ascii'), '<root', 'ascii'));
    }

    public static function __callStatic($name, $args): HTMLElement
    {
        return static::element($name, ...$args);
    }

    /**
     * @return  false[]
     *
     * @deprecated  Remove after HTML5 lib supports PHP8.4
     */
    protected static function getHTML5DefaultOptions(): array
    {
        return array(
            // Whether the serializer should aggressively encode all characters as entities.
            'encode_entities' => false,

            // Prevents the parser from automatically assigning the HTML5 namespace to the DOM document.
            'disable_html_ns' => false,
        );
    }

    /**
     * @deprecated  Remove after HTML5 lib supports PHP8.4
     */
    protected static function toHTML5(Node $dom, mixed $file, array $options = [])
    {
        $close = true;
        if (is_resource($file)) {
            $stream = $file;
            $close = false;
        } else {
            $stream = fopen($file, 'wb');
        }
        $options = array_merge(static::getHTML5DefaultOptions(), $options);
        $rules = new OutputRules($stream, $options);
        $trav = new Traverser($dom, $stream, $rules, $options);

        $trav->walk();
        /*
         * release the traverser to avoid cyclic references and allow PHP to free memory without waiting
         * for gc_collect_cycles
         */
        $rules->unsetTraverser();

        if ($close) {
            fclose($stream);
        }
    }

    /**
     * @deprecated  Remove after HTML5 lib supports PHP8.4
     */
    protected static function saveHTML5(Node $dom, array $options = []): string
    {
        $stream = fopen('php://temp', 'wb');
        static::toHTML5($dom, $stream, array_merge(static::getHTML5DefaultOptions(), $options));

        $html = stream_get_contents($stream, -1, 0);

        fclose($stream);

        return $html;
    }
}
