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
 * The JsonCastSearchable class.
 */
class JsonCastSearchable extends JsonCast
{
    public int $encodeOptions = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE;
}
