<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Schema\Ddl;

use Windwalker\Utilities\Arr;
use Windwalker\Utilities\StrNormalize;

/**
 * Trait WrapableTrait
 */
trait WrappableTrait
{
    /**
     * bind
     *
     * @param  array  $data
     *
     * @return  static
     */
    public function fill(array $data): static
    {
        foreach ($data as $key => $datum) {
            $prop = StrNormalize::toCamelCase(strtolower($key));

            if (method_exists($this, $prop)) {
                $this->$prop($datum);
            } elseif (property_exists($this, $prop)) {
                $this->$prop = $datum;
            } elseif (method_exists($this, 'setOption')) {
                $this->setOption($prop, $datum);
            }
        }

        return $this;
    }

    /**
     * wrap
     *
     * @param  array|static  $data
     *
     * @return  static
     */
    public static function wrap(mixed $data): static
    {
        if ($data instanceof static) {
            return $data;
        }

        return (new static())->fill($data);
    }

    /**
     * wrapList
     *
     * @param  array        $items
     * @param  string|null  $keyName
     *
     * @return  static[]
     */
    public static function wrapList(array $items, ?string $keyName = null): array
    {
        $newItems = [];

        foreach ($items as $key => $item) {
            $key = $keyName ? Arr::get($item, $keyName) : $key;

            $newItems[$key] = static::wrap($item);
        }

        return $newItems;
    }
}
