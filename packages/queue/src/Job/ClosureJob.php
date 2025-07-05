<?php

declare(strict_types=1);

namespace Windwalker\Queue\Job;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Windwalker\Queue\Attributes\JobBackoff;
use Windwalker\Utilities\Attributes\AttributesAccessor;

class ClosureJob
{
    /**
     * Property callable.
     *
     * @var  SerializableClosure
     */
    protected SerializableClosure $job;

    /**
     * CallableJob constructor.
     *
     * @param  Closure|SerializableClosure  $job
     */
    public function __construct(Closure|SerializableClosure $job)
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
     * Find Backoff attribute from job closure.
     *
     * @return  array|int|null
     *
     * @throws \ReflectionException
     */
    #[JobBackoff]
    public function backoff(): array|int|null
    {
        $attr = AttributesAccessor::getFirstAttributeInstance(
            $this->getJobClosure(),
            JobBackoff::class,
            \ReflectionAttribute::IS_INSTANCEOF
        );

        if (!$attr) {
            return null;
        }

        return $attr->backoff;
    }

    /**
     * @return SerializableClosure
     */
    public function getJob(): SerializableClosure
    {
        return $this->job;
    }

    /**
     * @return Closure
     */
    public function getJobClosure(): Closure
    {
        return $this->job->getClosure();
    }

    public function __invoke(JobController $controller): void
    {
        $callback = $this->getJobClosure();

        $callback($controller);
    }
}
