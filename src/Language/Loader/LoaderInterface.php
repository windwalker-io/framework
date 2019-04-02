<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Loader;

/**
 * Interface LoaderInterface
 */
interface LoaderInterface
{
    /**
     * getName
     *
     * @return  string
     */
    public function getName();

    /**
     * load
     *
     * @param string $file
     *
     * @return  string
     */
    public function load($file);
}
