<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Attributes;

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
     * @return  mixed
     */
    public function __invoke(AttributeHandler $handler);
}
