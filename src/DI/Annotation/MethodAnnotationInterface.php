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
 * Interface MethodAnnotationInterface
 *
 * @since  __DEPLOY_VERSION__
 */
interface MethodAnnotationInterface
{
    /**
     * handle
     *
     * @param Container         $container
     * @param \Closure          $closure
     * @param \ReflectionMethod $method
     *
     * @return  \Closure
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __invoke(Container $container, \Closure $closure, \ReflectionMethod $method);
}
