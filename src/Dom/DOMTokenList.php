<?php declare(strict_types=1);
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Dom;

/**
 * The DOMTokenList class.
 *
 * @since  3.5.3
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
     * @since  3.5.3
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
     * @since  3.5.3
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
     * @since  3.5.3
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
     * @since  3.5.3
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
     * @since  3.5.3
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
     * @since  3.5.3
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
     * @since  3.5.3
     */
    public function getHtml(): HtmlElement
    {
        return $this->html;
    }
}
