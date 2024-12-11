<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\Utilities\TypeCast;

/**
 * The ForceObjectSerializer class.
 */
#[\Attribute]
class ForceObjectJsonSerializer implements JsonSerializerInterface
{
    public function __construct(
        public bool $deep = false,
        public bool $nullable = false,
        public string $class = \stdClass::class
    ) {
    }

    public function serialize(mixed $data): mixed
    {
        if ($this->nullable && $data === null) {
            return null;
        }

        return TypeCast::toObject($data, $this->deep, $this->class);
    }
}
