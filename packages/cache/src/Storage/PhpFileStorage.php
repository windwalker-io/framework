<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Storage;

/**
 * The PhpFileStorage class.
 */
class PhpFileStorage extends FileStorage
{
    /**
     * read
     *
     * @param  string  $key
     *
     * @return  string
     */
    protected function read(string $key): string
    {
        return include $this->fetchStreamUri($key);
    }
}
