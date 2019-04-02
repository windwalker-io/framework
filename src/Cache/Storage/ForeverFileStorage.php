<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Cache\Storage;

/**
 * The ForeverFileStorage class.
 *
 * @since  3.2
 */
class ForeverFileStorage extends FileStorage
{
    /**
     * Check whether or not the cached data by id has expired.
     *
     * @param   string $key The storage entry identifier.
     *
     * @return  boolean  True if the data has expired.
     *
     * @since   3.2
     */
    public function isExpired($key)
    {
        return false;
    }
}
