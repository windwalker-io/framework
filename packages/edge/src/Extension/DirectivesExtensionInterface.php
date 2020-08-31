<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Extension;

/**
 * Interface DirectivesExtensionInterface
 */
interface DirectivesExtensionInterface extends EdgeExtensionInterface
{
    /**
     * getDirectives
     *
     * @return  callable[]
     */
    public function getDirectives(): array;
}
