<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Session\Bag;

/**
 * Interface SessionBagInterface
 */
interface SessionBagInterface
{
    /**
     * setData
     *
     * @param array $data
     *
     * @return  void
     */
    public function setData(array &$data);

    /**
     * get
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return  mixed
     */
    public function get($key, $default);

    /**
     * set
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return  static
     */
    public function set($key, $value);

    /**
     * has
     *
     * @param string $name
     *
     * @return  bool
     */
    public function has($name);

    /**
     * all
     *
     * @return  array
     */
    public function all();

    /**
     * clean
     *
     * @return  static
     */
    public function clear();
}

