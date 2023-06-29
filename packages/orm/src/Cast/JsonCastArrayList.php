<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

use JsonException;
use Windwalker\Utilities\TypeCast;

/**
 * The JsonCast class.
 */
class JsonCastArrayList extends JsonCast
{
    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function extract(mixed $value): mixed
    {
        if (is_json($value)) {
            return $value;
        }

        $value = array_values(TypeCast::toArray($value));

        return json_encode($value, $this->encodeOptions);
    }
}
