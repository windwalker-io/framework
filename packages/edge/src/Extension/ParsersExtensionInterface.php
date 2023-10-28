<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Extension;

/**
 * Interface ParsersExtensionInterface
 */
interface ParsersExtensionInterface extends EdgeExtensionInterface
{
    /**
     * getParsers
     *
     * @return  callable[]
     */
    public function getParsers(): array;
}
