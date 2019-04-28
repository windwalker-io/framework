<?php declare(strict_types=1);
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Dom;

use Windwalker\String\StringNormalise;

/**
 * The DOMStringMap class.
 *
 * @since  3.5.3
 */
class DOMStringMap
{
    /**
     * Property html.
     *
     * @var HtmlElement
     */
    protected $html;

    /**
     * ClassList constructor.
     *
     * @param HtmlElement $html
     */
    public function __construct(HtmlElement $html)
    {
        $this->html = $html;
    }

    /**
     * Method to get property Html
     *
     * @return  HtmlElement
     *
     * @since  3.5.3
     */
    public function getHtml(): HtmlElement
    {
        return $this->html;
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
        $attrs = array_filter($this->html->getAttributes(), static function ($v, $k) {
            return strpos($k, 'data-') === 0;
        }, ARRAY_FILTER_USE_BOTH);

        $dataAttrs = [];

        foreach ($attrs as $key => $value) {
            $key = substr($key, 5);

            $dataAttrs[$key] = $value;
        }

        return $dataAttrs;
    }

    /**
     * __get
     *
     * @param string $name
     *
     * @return  string
     *
     * @since  3.5.3
     */
    public function __get($name)
    {
        return $this->html->getAttribute($this->toDataKey($name));
    }

    /**
     * __set
     *
     * @param string $name
     * @param string $value
     *
     * @return  void
     *
     * @since  3.5.3
     */
    public function __set($name, $value)
    {
        $this->html->setAttribute($this->toDataKey($name), $value);
    }

    /**
     * __isset
     *
     * @param string $name
     *
     * @return  bool
     *
     * @since  3.5.3
     */
    public function __isset($name)
    {
        return $this->html->hasAttribute($this->toDataKey($name));
    }

    /**
     * __unset
     *
     * @param string $name
     *
     * @return  void
     *
     * @since  3.5.3
     */
    public function __unset($name)
    {
        $this->html->removeAttribute($this->toDataKey($name));
    }

    /**
     * toDataKey
     *
     * @param string $name
     *
     * @return  string
     *
     * @since  3.5.3
     */
    protected function toDataKey(string $name): string
    {
        return 'data-' . StringNormalise::toDashSeparated(strtolower(StringNormalise::fromCamelCase($name)));
    }
}
