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

        $data = TypeCast::toArray($data);
        $args = [];

        $ref = new \ReflectionClass(static::class);
        $constructor = $ref->getConstructor();

        if ($constructor) {
            foreach ($constructor->getParameters() as $parameter) {
                $name = $parameter->getName();

                if (array_key_exists($name, $data)) {
                    $args[] = $data[$name];

                    unset($data[$name]);
                }
            }
        }

        return new static(...$args)->fill($data);
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
