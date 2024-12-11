<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

interface JsonSerializerInterface
{
    public function serialize(mixed $data): mixed;
}
