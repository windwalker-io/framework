<?php

declare(strict_types=1);

namespace Windwalker\Data;

use Windwalker\Utilities\StrNormalize;
use Windwalker\Utilities\TypeCast;

trait ValueObjectTrait
{
    public static function wrap(mixed $data): static
    {
        if ($data instanceof static) {
            return $data;
        }

        return new static(...$data);
    }

    public static function tryWrap(mixed $data): ?static
    {
        if ($data === null) {
            return null;
        }

        return static::wrap($data);
    }

    public function fill(mixed $data): static
    {
        if (is_string($data) && is_json($data)) {
            $data = json_decode($data, true);
        }

        $values = TypeCast::toArray($data);

        foreach ($values as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    public function dump(bool $recursive = false): array
    {
        return TypeCast::toArray($this, $recursive);
    }
}
