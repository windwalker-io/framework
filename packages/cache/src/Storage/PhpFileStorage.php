<?php

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
     * @return  mixed
     */
    protected function read(string $key): mixed
    {
        return include $this->fetchStreamUri($key);
    }
}
