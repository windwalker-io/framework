<?php

declare(strict_types=1);

namespace Windwalker\Attributes;

/**
 * Interface AttributeInterface
 */
interface AttributeInterface
{
    /**
     * Run this attribute.
     *
     * @param  AttributeHandler  $handler
     *
     * @return  callable
     */
    public function __invoke(AttributeHandler $handler): callable;
}
