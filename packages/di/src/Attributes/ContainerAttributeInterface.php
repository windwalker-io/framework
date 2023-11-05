<?php

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

/**
 * Interface ContainerAttributeInterface
 */
interface ContainerAttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable;
}
