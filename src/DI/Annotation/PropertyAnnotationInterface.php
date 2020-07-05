<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2020 .
 * @license    __LICENSE__
 */

namespace Windwalker\DI\Annotation;

use Windwalker\DI\Container;

/**
 * Interface PropertyAnnotationInterface
 *
 * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
     */
    public function __invoke(Container $container, $instance, \ReflectionProperty $property);
}
