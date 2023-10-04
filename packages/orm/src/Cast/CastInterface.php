<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

/**
 * Interface CastInterface
 */
interface CastInterface
{
    public const TYPE_CAST = 1;

    public const CONSTRUCTOR = 2;

    public const HYDRATE = 3;

    /**
     * Cast to php type or object.
     *
     * @param  mixed  $value
     *
     * @return  mixed
     */
    public function hydrate(mixed $value): mixed;

    /**
     * Extract from php type or object to string or NULL for storing.
     *
     * @param  mixed  $value
     *
     * @return  mixed
     */
    public function extract(mixed $value): mixed;
}
