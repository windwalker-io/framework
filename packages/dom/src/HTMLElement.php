<?php

declare(strict_types=1);

namespace Windwalker\DOM;

use Dom\Attr;
use Dom\Document;
use Dom\HTMLElement as NativeHTMLElement;
use Dom\Node;
use Dom\NodeList;
use DOMException;
use JetBrains\PhpStorm\ArrayShape;
use Masterminds\HTML5;
use Symfony\Component\DomCrawler\Crawler;
use Windwalker\Utilities\Str;

use function Windwalker\value;

/**
 * The HTMLElement class.
 */
class HTMLElement extends NativeHTMLElement implements \ArrayAccess
{
    public protected(set) CSSStyleDeclaration $style {
        get => $this->style ??= new CSSStyleDeclaration($this);
    }

    public protected(set) DOMStringMap $dataset {
        get => $this->dataset ??= new DOMStringMap($this);
    }

    /**
     * @param  string      $name
     * @param  array       $attributes
     * @param  mixed|null  $content
     *
     * @return  DOMElement
     *
     * @deprecated  Use HTMLElement::new() instead.
     */
    #[\Deprecated(message: 'Use HTMLElement::new() instead.')]
    public static function create(string $name, array $attributes = [], mixed $content = null): DOMElement
    {
        return DOMElement::create($name, $attributes, $content)->asHTML();
    }

    public static function new(string $name, array $attributes = [], mixed $content = null): HTMLElement
    {
        return HTML5Factory::element($name, $attributes, $content);
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
            /** @var \DOMDocumentFragment $fragment */
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

    public function appendChild(mixed $newnode): Node
    {
        if ($newnode instanceof \DOMElement) {
            $newnode = $this->ownerDocument->importLegacyNode($newnode, true);
        }

        if (!$newnode instanceof Node) {
            return self::insertContentTo($newnode, $this);
        }

        if (!$this->ownerDocument->isSameNode($newnode->ownerDocument)) {
            $newnode = $this->ownerDocument->importNode($newnode->cloneNode(true), true);
        }

        return parent::appendChild($newnode);
    }

    public function appendText(string $string): \DOMText
    {
        return static::insertContentTo($string, $this);
    }

    /**
     * render
     *
     * @param  bool  $formatOutput
     *
     * @return  string
     */
    public function render(bool $formatOutput = false): string
    {
        if (!$this->ownerDocument) {
            throw new \LogicException('Please attach Element to a Document before render it.');
        }

        // $formatOutputBak = $this->ownerDocument->formatOutput;
        //
        // $this->ownerDocument->formatOutput = $formatOutput;

        $result = HTML5Factory::saveHtml($this);

        // $this->ownerDocument->formatOutput = $formatOutputBak;

        return $result;
    }

    /**
     * Convert this object to string.
     *
     * @return  string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @param  bool  $toString
     *
     * @return  array<string|Attr>
     */
    public function getAttributes(bool $toString = false): array
    {
        if ($this->attributes === null) {
            return [];
        }

        $attrs = iterator_to_array($this->attributes);

        if (!$toString) {
            return $attrs;
        }

        return array_map(
            static function (Attr $attr) {
                return $attr->value;
            },
            $attrs
        );
    }

    /**
     * Set all attributes.
     *
     * @param  array  $attribs  All attributes.
     *
     * @return  static  Return self to support chaining.
     * @throws DOMException
     */
    public function setAttributes(array $attribs): static
    {
        foreach ($attribs as $key => $attribute) {
            $this->setAttribute($key, $attribute);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAttribute(string $qualifiedName, mixed $value): void
    {
        if ($value === true) {
            parent::setAttribute($qualifiedName, '');
            return;
        }

        if ($value === null || $value === false) {
            $this->removeAttribute($qualifiedName);

            return;
        }

        try {
            parent::setAttribute($qualifiedName, static::valueToString($value));
        } catch (DOMException $e) {
            if ($e->getCode() === 5) {
                throw new DOMException(
                    $e->getMessage() . ' for attribute: "' . $qualifiedName . '"',
                    $e->getCode(),
                    $e
                );
            } else {
                throw $e;
            }
        }
    }

    /**
     * getCrawler
     *
     * @return  Crawler
     */
    public function getCrawler(): Crawler
    {
        if (!class_exists(Crawler::class)) {
            throw new \LogicException('Please install symfony/dom-crawler first.');
        }

        return new Crawler($this);
    }

    /**
     * Get element tag name.
     *
     * @return  string
     */
    public function getName(): string
    {
        return $this->tagName;
    }

    /**
     * Whether a offset exists
     *
     * @param  mixed  $offset  An offset to check for.
     *
     * @return bool True on success or false on failure.
     *                 The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->hasAttribute($offset);
    }

    /**
     * Offset to retrieve
     *
     * @param  mixed  $offset  The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Offset to set
     *
     * @param  mixed  $offset  The offset to assign the value to.
     * @param  mixed  $value   The value to set.
     *
     * @return void
     * @throws DOMException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @param  mixed  $offset  The offset to unset.
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->removeAttribute($offset);
    }

    /**
     * Use another root document.
     *
     * @param  Node  $node
     * @param  bool     $deep
     *
     * @return  static|NativeHTMLElement
     */
    public function with(Node $node, bool $deep = true): NativeHTMLElement|static
    {
        if ($node instanceof Document) {
            $dom = $node;
        } else {
            $dom = $node->ownerDocument;
        }

        return $dom->importNode($this, $deep);
    }

    public function createChild(string $name, array $attributes = [], $content = null): Node|static
    {
        $ele = static::create($name, $attributes, $content);

        return $this->appendChild($ele);
    }

    public static function buildAttributes(array|\DOMElement|NativeHTMLElement $attributes): string
    {
        if ($attributes instanceof NativeHTMLElement) {
            $attributes = array_map(
                static fn(Attr $attr) => $attr->value,
                iterator_to_array($attributes->attributes)
            );
        }

        if ($attributes instanceof \DOMElement) {
            $attributes = array_map(
                static fn(\DOMAttr $attr) => $attr->value,
                iterator_to_array($attributes->attributes)
            );
        }

        $ele = static::new('root', $attributes, '')->render(false);

        return trim(Str::removeLeft(Str::removeRight($ele, '></root>', 'ascii'), '<root', 'ascii'));
    }

    public function attributesToString(?string $type = null): string
    {
        return static::buildAttributes($this->getAttributes(), $type);
    }

    /**
     * addClass
     *
     * @param  string  $class
     *
     * @return  static
     *
     * @since  3.5.3
     */
    public function addClass(string $class): static
    {
        $classes = array_filter(explode(' ', $class), 'strlen');

        $this->classList->add(...$classes);

        return $this;
    }

    public function removeClass(string $class): static
    {
        $classes = array_filter(explode(' ', $class), 'strlen');

        $this->classList->remove(...$classes);

        return $this;
    }

    public function toggleClass(string $class, ?bool $force = null): static
    {
        $this->classList->toggle($class, $force);

        return $this;
    }

    public function hasClass(string $class): static
    {
        $this->classList->contains($class);

        return $this;
    }

    public static function fromLegacyElement(\DOMElement $ele): HTMLElement
    {
        // $ele = HTML5Factory::createFromString($eleText)->documentElement;
        $ele = HTML5Factory::document()->importLegacyNode($ele, true);

        $attrs = [];

        foreach ($ele->getAttributeNames() as $attributeName) {
            $attrs[$attributeName] = $ele->getAttribute($attributeName);
        }

        return static::new(
            $ele->nodeName,
            $attrs,
            $ele->childNodes
        );
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
}
