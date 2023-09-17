<?php

/**
 * Part of cati project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\ORM\Cast\CastInterface;

/**
 * The JsonSerializer class.
 */
#[\Attribute]
class JsonSerializer
{
    public function __construct(public mixed $handler)
    {
    }

    public function serialize(mixed $data): mixed
    {
        $handler = $this->handler;

        if (is_a($handler, CastInterface::class)) {
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
