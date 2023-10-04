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
 * Interface GlobalVariablesExtensionInterface
 */
interface GlobalVariablesExtensionInterface extends EdgeExtensionInterface
{
    /**
     * getGlobals
     *
     * @return  array
     */
    public function getGlobals(): array;
}
