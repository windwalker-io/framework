<?php

declare(strict_types=1);

namespace Windwalker\Queue\Job;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Windwalker\Queue\Attributes\JobAfterProcess;
use Windwalker\Queue\Attributes\JobBeforeProcess;
use Windwalker\Queue\Attributes\JobFailed;

class JobWrapper implements JobWrapperInterface
{
    /**
     * Property callable.
     *
     * @var  callable
     */
    protected mixed $job;

    /**
     * CallableJob constructor.
     *
     * @param  callable  $job
     * @param  string|null  $name
     */
    public function __construct(callable $job)
    {
        if ($job instanceof Closure) {
            if (!class_exists(SerializableClosure::class)) {
                throw new \DomainException('Please install `laravel/serializable-closure` first');
            }

            $job = new SerializableClosure($job);
        }

        $this->job = $job;
    }

    /**
     * @return callable
     */
    public function getJob(): callable
    {
        return $this->job;
    }

    public function process(JobController $controller): void
    {
        $callback = $this->job;

        if ($callback instanceof SerializableClosure) {
            $callback = $callback->getClosure();
        }

        $callback($controller);
    }

    public function failed(\Throwable $e): void
    {
        $job = $this->getJob();

        if (!is_object($job)) {
            return;
        }

        if ($method = static::findMethodWithAttribute($job, JobFailed::class)) {
            $method->invoke($job, $e);
        } elseif (method_exists($job, 'failed')) {
            $job->failed($e);
        }
    }

    protected static function findMethodWithAttribute(object $obj, string $attribute): ?\ReflectionMethod
    {
        $reflection = new \ReflectionObject($obj);

        return array_find(
            $reflection->getMethods(),
            fn($method) => $method->getAttributes($attribute, \ReflectionAttribute::IS_INSTANCEOF)
        );
    }

    public function beforeProcess(JobController $controller): void
    {
        $job = $this->getJob();

        if (
            is_object($job)
            && $method = static::findMethodWithAttribute($job, JobBeforeProcess::class)
        ) {
            $method->invoke($job, $controller);
        }
    }

    public function afterProcess(JobController $controller): void
    {
        $job = $this->getJob();

        if (
            is_object($job)
            && $method = static::findMethodWithAttribute($job, JobAfterProcess::class)
        ) {
            $method->invoke($job, $controller);
        }
    }
}
