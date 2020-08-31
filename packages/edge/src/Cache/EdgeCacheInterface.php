<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Cache;

/**
 * Interface EdgeCacheInterface
 *
 * @since  3.0
 */
interface EdgeCacheInterface
{
    /**
     * isExpired
     *
     * @param  string  $path
     *
     * @return bool
     */
    public function isExpired(string $path): bool;

    /**
     * getCacheKey
     *
     * @param  string  $path
     *
     * @return  string
     */
    public function getCacheKey(string $path): string;

    /**
     * get
     *
     * @param  string  $path
     *
     * @return  string
     */
    public function load(string $path): string;

    /**
     * store
     *
     * @param  string  $path
     * @param  string  $value
     *
     * @return void
     */
    public function store(string $path, string $value): void;

    /**
     * remove
     *
     * @param  string  $path
     *
     * @return void
     */
    public function remove(string $path): void;
}
