<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DI;

/**
 * Interface ContainerAwareInterface
 */
interface ContainerAwareInterface
{
    /**
     * Get the DI container.
     *
     * @return  Container
     *
     * @throws  \UnexpectedValueException May be thrown if the container has not been set.
     */
    public function getContainer();

    /**
     * Set the DI container.
     *
     * @param   Container $container The DI container.
     *
     * @return  mixed
     */
    public function setContainer(Container $container);
}

