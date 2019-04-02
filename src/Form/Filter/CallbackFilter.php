<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form\Filter;

/**
 * The CallbackFilter class.
 *
 * @since  3.2
 */
class CallbackFilter implements FilterInterface
{
    /**
     * Property handler.
     *
     * @var  callable
     */
    protected $handler;

    /**
     * CallbackFilter constructor.
     *
     * @param callable $handler
     */
    public function __construct(callable $handler = null)
    {
        $this->handler = $handler;
    }

    /**
     * clean
     *
     * @param string $text
     *
     * @return  string
     */
    public function clean($text)
    {
        if (!$this->handler) {
            return $text;
        }

        $handler = $this->handler;

        return $handler($text);
    }

    /**
     * Method to get property Handler
     *
     * @return  callable
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Method to set property handler
     *
     * @param   callable $handler
     *
     * @return  static  Return self to support chaining.
     */
    public function setHandler(callable $handler)
    {
        $this->handler = $handler;

        return $this;
    }
}
