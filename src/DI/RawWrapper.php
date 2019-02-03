<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\DI;

/**
 * The RawWrapper class.
 *
 * @since  __DEPLOY_VERSION__
 */
class RawWrapper
{
    /**
     * Property value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * RawWrapper constructor.
     *
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * get
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * set
     *
     * @param mixed $value
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function set($value): self
    {
        $this->value = $value;

        return $this;
    }
}
