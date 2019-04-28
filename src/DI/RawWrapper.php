<?php declare(strict_types=1);
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\DI;

/**
 * The RawWrapper class.
 *
 * @since  3.5.1
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
     * @since  3.5.1
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
     * @since  3.5.1
     */
    public function set($value): self
    {
        $this->value = $value;

        return $this;
    }
}
