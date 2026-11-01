<?php

declare(strict_types=1);

namespace Windwalker\Queue\Attributes;

use Windwalker\Queue\Job\JobController;
use Windwalker\Utilities\Attributes\AttributesAccessor;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
class JobBackoff
{
    /**
     * @param  array|int|null  $backoff
     * @param  float|null      $jitter   Ratio between 0 and 1, delay is multiplied by [1 - jitter, 1 + jitter].
     */
    public function __construct(
        public array|int|null $backoff = null,
        public ?float $jitter = null,
    ) {}

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

        $factor = $pow === 1 ? $n + 1 : $pow ** $n;

        return min($maxSeconds, $base * $factor * $adaptive);
    }

    public static function resolve(int $attempts, array|int|false|null $backoff): int|false|null
    {
        if (is_array($backoff)) {
            return $backoff[$attempts - 1] ?? false;
        }

        return $backoff;
    }

    public static function applyJitter(int $seconds, ?float $jitter): int
    {
        if (!$jitter) {
            return $seconds;
        }

        $jitter = min(1.0, abs($jitter));
        $factor = 1 + (random_int(-1000000, 1000000) / 1000000) * $jitter;

        return max(0, (int) round($seconds * $factor));
    }

    public static function jitterFromController(JobController $controller): ?float
    {
        $attr = AttributesAccessor::getFirstAttributeInstance(
            $controller->job,
            static::class,
            \ReflectionAttribute::IS_INSTANCEOF
        );

        if ($attr) {
            return $attr->jitter;
        }

        foreach (new \ReflectionObject($controller->job)->getMethods() as $method) {
            if ($attrs = $method->getAttributes(static::class, \ReflectionAttribute::IS_INSTANCEOF)) {
                return $attrs[0]->newInstance()->jitter;
            }
        }

        return null;
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
