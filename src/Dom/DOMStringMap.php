<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    __LICENSE__
 */

namespace Windwalker\Dom;

use Windwalker\String\StringNormalise;

/**
 * The DOMStringMap class.
 *
 * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
     */
    protected function toDataKey(string $name): string
    {
        return 'data-' . StringNormalise::toDashSeparated(strtolower(StringNormalise::fromCamelCase($name)));
    }
}
