<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

/**
 * The JsonCastSearchable class.
 */
class JsonCastSearchable extends JsonCast
{
    public int $encodeOptions = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE;
}
