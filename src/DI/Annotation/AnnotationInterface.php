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
 * Interface AnnotationInterface
 *
 * @since  __DEPLOY_VERSION__
 */
interface AnnotationInterface
{
    /**
     * handle
     *
     * @param Container  $container
     * @param object     $instance
     * @param \Reflector $reflector
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __invoke(Container $container, $instance, \Reflector $reflector);
}
