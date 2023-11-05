<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Proxy;

/**
 * The DisposableCallable class.
 */
class DisposableCallable extends CallableProxy
{
    /**
     * @var bool
     */
    protected bool $called = false;

    /**
     * @inheritDoc
     */
    public function __invoke(...$args): mixed
    {
        if ($this->called) {
            return null;
        }

        $value = parent::__invoke(...$args);

        $this->called = true;

        return $value;
    }

    /**
     * Method to get property Called
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isCalled(): bool
    {
        return $this->called;
    }
}
