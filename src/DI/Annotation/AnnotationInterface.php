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
 * Interface AnnotationInterface
 *
 * @since  3.5.19
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
     * @since  3.5.19
     */
    public function __invoke(Container $container, $instance, \Reflector $reflector);
}
