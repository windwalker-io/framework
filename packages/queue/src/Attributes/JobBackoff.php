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

    /**
     * - linear = pow: 1 | (1, 2, 3, 4, 5, 6, ...)
     * - exponential = pow: 2 | (1, 2, 4, 8, 16, 32, ...)
     * - polynomial = pow: >= 3 | (1, 2, 9, 27, 81, 243, ...)
     * - adaptive = pow: >= 2, adaptive: > 1 | (1, 4, 9, 16, 25, 36, ...)
     *
     * @param  int       $n
     * @param  int       $base
     * @param  int       $pow
     * @param  int|null  $maxTimes
     * @param  int       $maxSeconds
     * @param  int       $adaptive
     *
     * @return  int|false
     */
    public static function backoff(
        int $n,
        int $base = 1,
        int $pow = 2,
        ?int $maxTimes = null,
        int $maxSeconds = 86400,
        int $adaptive = 1,
    ): int|false {
        if ($maxTimes !== null && $n >= $maxTimes) {
            return false;
        }

        return min($maxSeconds, $base * ($pow ** $n) * $adaptive);
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
