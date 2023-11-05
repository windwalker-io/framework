<?php

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
