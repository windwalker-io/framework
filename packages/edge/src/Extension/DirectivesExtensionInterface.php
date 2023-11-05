<?php

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
