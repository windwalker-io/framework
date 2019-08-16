<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    __LICENSE__
 */

namespace Windwalker\Query;

/**
 * The FormatWrapper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class FormatWrapper
{
    /**
     * Property string.
     *
     * @var string
     */
    protected $string = '';

    /**
     * Property args.
     *
     * @var  array
     */
    protected $args = [];

    /**
     * FormatWrapper constructor.
     *
     * @param string $string
     * @param mixed  ...$args
     */
    public function __construct(string $string, ...$args)
    {
        $this->string = $string;
        $this->args   = $args;
    }

    /**
     * getAll
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function all(): array
    {
        $args = $this->args;

        array_unshift($args, $this->string);

        return $args;
    }

    /**
     * Method to get property String
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * Method to get property Args
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * toString
     *
     * @param Query $query
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function toString(Query $query): string
    {
        return $query->format($this->string, ...$this->args);
    }
}
