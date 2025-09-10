<?php

declare(strict_types=1);

namespace Windwalker\DOM;

use Windwalker\Utilities\StrNormalize;

/**
 * The DOMStringMap class.
 *
 * @since  3.5.3
 */
class DOMStringMap
{
    public function __construct(protected DOMElement|HTMLElement $element)
    {
    }

    /**
     * Method to get property Html
     *
     * @return  DOMElement|HTMLElement
     *
     * @since  3.5.3
     */
    public function getDOMElement(): DOMElement|HTMLElement
    {
        return $this->element;
    }

    /**
     * getDataAttrs
     *
     * @return  array
     *
     * @since  3.5.3
     */
    protected function getDataAttrs(): array
    {
        $attrs = array_filter(
            $this->element->getAttributes(),
            static fn($v, $k) => str_starts_with($k, 'data-'),
            ARRAY_FILTER_USE_BOTH
        );

        $dataAttrs = [];

        foreach ($attrs as $key => $value) {
            $key = substr($key, 5);

            $dataAttrs[$key] = $value;
        }

        return $dataAttrs;
    }

    /**
     * @param  string  $name
     *
     * @return  string
     *
     * @since  3.5.3
     */
    public function __get(string $name): string
    {
        return $this->element->getAttribute($this->toDataKey($name));
    }

    /**
     * @param  string  $name
     * @param  string  $value
     *
     * @return  void
     *
     * @since  3.5.3
     */
    public function __set(string $name, mixed $value)
    {
        $this->element->setAttribute($this->toDataKey($name), (string) $value);
    }

    /**
     * @param  string  $name
     *
     * @return  bool
     *
     * @since  3.5.3
     */
    public function __isset(string $name): bool
    {
        return $this->element->hasAttribute($this->toDataKey($name));
    }

    /**
     * @param  string  $name
     *
     * @return  void
     *
     * @since  3.5.3
     */
    public function __unset(string $name)
    {
        $this->element->removeAttribute($this->toDataKey($name));
    }

    /**
     * toDataKey
     *
     * @param  string  $name
     *
     * @return  string
     *
     * @since  3.5.3
     */
    private function toDataKey(string $name): string
    {
        return 'data-' . StrNormalize::toDashSeparated($name);
    }
}
