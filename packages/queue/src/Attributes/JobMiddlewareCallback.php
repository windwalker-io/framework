<?php

declare(strict_types=1);

namespace Windwalker\Queue\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class JobMiddlewareCallback
{
    public function __construct(public ?int $order = null)
    {
    }
}
