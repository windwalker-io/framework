<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class JsonNoSerialize implements JsonSerializerInterface
{
    public function serialize(mixed $data): mixed
    {
        return $data;
    }
}
