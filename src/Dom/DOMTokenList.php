<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    __LICENSE__
 */

namespace Windwalker\Dom;

/**
 * The DOMTokenList class.
 *
 * @since  __DEPLOY_VERSION__
 */
class DOMTokenList
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
     * add
     *
     * @param string ...$args
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function add(string ...$args): self
    {
        $classes = $this->getClasses();

        $classes = array_values(array_unique(array_merge($classes, $args)));

        $this->html->setAttribute('class', implode(' ', $classes));

        return $this;
    }

    /**
     * remove
     *
     * @param string ...$args
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function remove(string ...$args): self
    {
        $classes = $this->getClasses();

        $classes = array_diff($classes, $args);

        $this->html->setAttribute('class', implode(' ', $classes));

        return $this;
    }

    /**
     * item
     *
     * @param int $index
     *
     * @return  string|null
     *
     * @since  __DEPLOY_VERSION__
     */
    public function item(int $index): ?string
    {
        $classes = $this->getClasses();

        return $classes[$index] ?? null;
    }

    /**
     * toggle
     *
     * @param string    $class
     * @param bool|null $force
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function toggle(string $class, ?bool $force = null): bool
    {
        if ($force === null) {
            $classes = $this->getClasses();

            if (in_array($class, $classes, true)) {
                $this->remove($class);

                return false;
            }

            $this->add($class);

            return true;
        }

        if ($force === true) {
            $this->add($class);

            return true;
        }

        $this->remove($class);

        return false;
    }

    /**
     * contains
     *
     * @param string $class
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function contains(string $class): bool
    {
        return in_array($class, $this->getClasses(), true);
    }

    /**
     * getClasses
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getClasses(): array
    {
        return array_filter(explode(' ', $this->html->getAttribute('class', '')), 'strlen');
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
}
