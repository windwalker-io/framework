<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Exception;

/**
 * The CacheExceptionInterface class.
 */
interface CacheExceptionInterface extends
    \Psr\Cache\CacheException,
    \Psr\SimpleCache\CacheException
{
}
