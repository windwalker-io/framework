<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

/**
 * Interface ContainerAttributeInterface
 */
interface ContainerAttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable;
}
