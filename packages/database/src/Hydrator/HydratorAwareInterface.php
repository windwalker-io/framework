<?php

declare(strict_types=1);

namespace Windwalker\Database\Hydrator;

/**
 * Interface HydratorAwareInterface
 */
interface HydratorAwareInterface
{
    /**
     * Get Hydrator object.
     *
     * @return  HydratorInterface
     */
    public function getHydrator(): HydratorInterface;
}
