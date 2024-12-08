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
    protected static ?HTMLDocument $dom = null;

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

    #[ArrayShape(['name' => "mixed|string", 'id' => "null|string", 'class' => "null|string"])]
    public static function splitCSSSelector(
        string $name
    ): array {
        $tokens = preg_split(
            '/([\.#]?[^\s#.]+)/',
            $name,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
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
                iterator_to_array($attributes->attributes)
            );
        }

        $ele = static::element('root', $attributes, '')->render();

        return trim(Str::removeLeft(Str::removeRight($ele, '></root>', 'ascii'), '<root', 'ascii'));
    }

    public static function __callStatic($name, $args): HTMLElement
    {
        return static::element($name, ...$args);
    }
}
