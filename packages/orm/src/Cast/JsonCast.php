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
class JsonCast implements CastInterface
{
    public const EMPTY_ARRAY_AS_OBJECT = 1 << 0;

    public const FORCE_ARRAY_LIST = 1 << 1;

    public function __construct(
        public int $options = self::EMPTY_ARRAY_AS_OBJECT,
        public int $encodeOptions = JSON_THROW_ON_ERROR,
        public int $decodeOptions = JSON_THROW_ON_ERROR,
    ) {
        //
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function hydrate(mixed $value): mixed
    {
        if (!is_string($value)) {
            if ($this->options & static::FORCE_ARRAY_LIST) {
                $value = array_values(TypeCast::toArray($value));
            }

            return $value;
        }

        if ($value === '') {
            return null;
        }

        return json_decode($value, true, 512, $this->decodeOptions) ?: [];
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function extract(mixed $value): mixed
    {
        if (is_json($value)) {
            return $value;
        }

        if ($value === [] && ($this->options & static::EMPTY_ARRAY_AS_OBJECT)) {
            $value = new \stdClass();
        }

        if ($this->options & static::FORCE_ARRAY_LIST) {
            $value = array_values(TypeCast::toArray($value));
        }

        return json_encode($value, $this->encodeOptions);
    }
}
