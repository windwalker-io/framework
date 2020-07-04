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
     * @param Container $container
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function handle(Container $container): void;
}
