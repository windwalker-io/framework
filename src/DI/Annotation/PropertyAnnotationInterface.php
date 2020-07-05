<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2020 .
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\DI\Annotation;

use Windwalker\DI\Container;

/**
 * Interface PropertyAnnotationInterface
 *
 * @since  3.5.19
 */
interface PropertyAnnotationInterface
{
    /**
     * handle
     *
     * @param Container           $container
     * @param object              $instance
     * @param \ReflectionProperty $property
     *
     * @return  object
     *
     * @since  3.5.19
     */
    public function __invoke(Container $container, $instance, \ReflectionProperty $property);
}
