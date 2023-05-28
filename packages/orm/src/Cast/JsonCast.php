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

/**
 * The JsonCast class.
 */
class JsonCast implements CastInterface
{
    public int $decodeOptions = JSON_THROW_ON_ERROR;

    public int $encodeOptions = JSON_THROW_ON_ERROR;

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function hydrate(mixed $value): mixed
    {
        if (!is_string($value)) {
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
        if ($value === []) {
            $value = new \stdClass();
        }

        return is_json($value) ? $value : json_encode($value, $this->encodeOptions);
    }
}
