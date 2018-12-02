<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Edge\Loader;

/**
 * Interface EdgeLoaderInterface
 *
 * @since  3.0
 */
interface EdgeLoaderInterface
{
    /**
     * load
     *
     * @param   string $key
     *
     * @return  string
     */
    public function find($key);
}
