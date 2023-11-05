<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Proxy;

/**
 * The StringableCallable class.
 */
class StringableCallable extends CallableProxy
{
    /**
     * __toString
     *
     * @return  string
     */
    public function __toString(): string
    {
        return (string) $this();
    }
}
