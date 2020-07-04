<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2020 .
 * @license    __LICENSE__
 */

namespace Windwalker\DI\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * The AnnotationRegistry class.
 *
 * @since  __DEPLOY_VERSION__
 */
class AnnotationRegistry
{
    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * Method to get property AnnotationReader
     *
     * @return  AnnotationReader
     *
     * @since  3.4.4
     *
     * @throws AnnotationException
     */
    public function getAnnotationReader(): AnnotationReader
    {
        if (!$this->annotationReader) {
            $this->annotationReader = new AnnotationReader();
        }

        return $this->annotationReader;
    }

    public function register(string $className)
    {

    }
}
