<?php

declare(strict_types=1);

namespace Windwalker\Queue\Attributes;

use Windwalker\Queue\Job\JobController;
use Windwalker\Utilities\Attributes\AttributesAccessor;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
class JobBackoff
{
    public function __construct(public array|int|null $backoff = null)
    {
    }

    public static function backoff(int $current, int $base = 1, ?int $maxTimes = null, int $pow = 2): int|false
    {
        if ($maxTimes !== null && $current >= $maxTimes) {
            return false;
        }

        return $base * ($pow ** $current);
    }

    public static function resolve(int $attempts, array|int|null|false $backoff): int|null|false
    {
        if (is_array($backoff)) {
            return $backoff[$attempts - 1] ?? false;
        }

        return $backoff;
    }

    public static function fromController(JobController $controller): false|int|null
    {
        // Find Attribute if set on the job class.
        if (
            $attr = AttributesAccessor::getFirstAttributeInstance(
                $controller->job,
                static::class,
                \ReflectionAttribute::IS_INSTANCEOF
            )
        ) {
            $backoff = $attr->backoff;
        } else {
            // Find Attribute if set on the method, only get once.
            $backoff = $controller->invokeMethodsWithAttribute(static::class)->current();
        }

        return static::resolve($controller->attempts, $backoff);
    }
}
