<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\ORM\Cast\CastInterface;

/**
 * The JsonSerializer class.
 */
#[\Attribute]
class JsonSerializer implements JsonSerializerInterface
{
    public function __construct(public mixed $handler)
    {
    }

    public function serialize(mixed $data): mixed
    {
        $handler = $this->handler;

        if (is_a($handler, CastInterface::class, true)) {
            $handler = new ($this->handler)();
        }

        if ($handler instanceof CastInterface) {
            return $handler->extract($data);
        }

        if (is_callable($handler)) {
            return $handler($data);
        }

        return new $handler($data);
    }
}
