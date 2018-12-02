<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Loader;

/**
 * Class PhpLoader
 *
 * @since 2.0
 */
class PhpLoader extends FileLoader
{
    /**
     * load
     *
     * @param string $file
     *
     * @throws \RuntimeException
     * @return  null|string
     */
    public function load($file)
    {
        if (!is_file($file)) {
            if (!$file = $this->findFile($file)) {
                throw new \RuntimeException(sprintf('Language file: %s not found.', $file));
            }
        }

        return include $file;
    }
}
