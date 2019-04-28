<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Utilities\Classes;

/**
 * The OptionAccessTrait class.
 *
 * @since  3.0.1
 */
trait OptionAccessTrait
{
    /**
     * Property options.
     *
     * @var  array|\ArrayAccess
     */
    protected $options = [];

    /**
     * Method to get property Options
     *
     * @param   string $name
     * @param   mixed  $default
     *
     * @return  mixed
     */
    public function getOption($name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * Method to set property options
     *
     * @param   string $name
     * @param   mixed  $value
     *
     * @return  static  Return self to support chaining.
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Method to get property Options
     *
     * @return  array|\ArrayAccess
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Method to set property options
     *
     * @param   array|\ArrayAccess $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * pushOption
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return  $this
     *
     * @since  3.5.1
     */
    public function pushOption(string $name, $value)
    {
        $array = $this->options[$name] ?? [];

        $array[] = $value;

        $this->options[$name] = $array;

        return $this;
    }
}
