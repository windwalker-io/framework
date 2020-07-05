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
 * Interface ClassAnnotationInterface
 *
 * @since  __DEPLOY_VERSION__
 */
interface ClassAnnotationInterface
{
    /**
     * handle
     *
     * @param Container        $container
     * @param object           $instance
     * @param \ReflectionClass $reflector
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __invoke(Container $container, $instance, \ReflectionClass $reflector);
}
