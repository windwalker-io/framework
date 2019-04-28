<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\DI;

/**
 * Interface ServiceProviderInterface
 */
interface ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container $container The DI container.
     *
     * @return  void
     */
    public function register(Container $container);
}
