<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2020 .
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\DI\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry as DoctrinAnnotationRegistry;
use Windwalker\DI\Container;

/**
 * The AnnotationRegistry class.
 *
 * @since  3.5.19
 */
class AnnotationRegistry
{
    protected static $inited = false;

    /**
     * @var array
     */
    protected $annotations = [];

    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * AnnotationRegistry constructor.
     */
    public function __construct()
    {
        if (!static::$inited) {
            DoctrinAnnotationRegistry::registerLoader('class_exists');
            static::$inited = true;
        }
    }

    /**
     * isSupported
     *
     * @return  bool
     *
     * @since  3.5.19
     */
    public static function isSupported(): bool
    {
        return class_exists(AnnotationReader::class);
    }

    /**
     * Method to get property AnnotationReader
     *
     * @return  AnnotationReader
     *
     * @throws AnnotationException
     * @since  3.4.4
     *
     */
    public function getAnnotationReader(): AnnotationReader
    {
        if (!$this->annotationReader) {
            $this->annotationReader = new AnnotationReader();
        }

        return $this->annotationReader;
    }

    /**
     * reflect
     *
     * @param object $object
     *
     * @return  \ReflectionObject
     *
     * @since  3.5.19
     */
    public function reflect($object): \ReflectionObject
    {
        if (!$object instanceof \ReflectionObject) {
            $object = new \ReflectionObject($object);
        }

        return $object;
    }

    /**
     * resolveClass
     *
     * @param Container $container
     * @param object    $instance
     *
     * @return  mixed
     *
     * @throws \ReflectionException|AnnotationException
     *
     * @since  3.5.19
     */
    public function resolveClass(Container $container, $instance)
    {
        $ref = new \ReflectionClass($instance);

        foreach ($this->getAnnotationReader()->getClassAnnotations($ref) as $annotation) {
            if (!is_callable($annotation)) {
                $class = get_class($annotation);
                throw new \LogicException("Annotation: {$class} is not callable.");
            }

            $instance = $annotation($container, $instance, $ref) ?? $instance;
        }

        return $instance;
    }

    /**
     * resolveMethod
     *
     * @param Container $container
     * @param object    $instance
     * @param string    $methodName
     * @param \Closure  $closure
     *
     * @return  \Closure
     *
     * @throws AnnotationException
     * @throws \ReflectionException
     *
     * @since  3.5.19
     */
    public function resolveMethod(Container $container, $instance, string $methodName, \Closure $closure): \Closure
    {
        $method = new \ReflectionMethod($instance, $methodName);

        foreach ($this->getAnnotationReader()->getMethodAnnotations($method) as $annotation) {
            if (!is_callable($annotation)) {
                $class = get_class($annotation);
                throw new \LogicException("Annotation: {$class} is not callable.");
            }

            $closure = $annotation($container, $closure, $method) ?? $closure;
        }

        return $closure;
    }

    /**
     * resolveProperties
     *
     * @param Container $container
     * @param object    $instance
     *
     * @return  object
     *
     * @throws AnnotationException
     * @since  3.5.19
     */
    public function resolveProperties(Container $container, $instance)
    {
        $ref = new \ReflectionObject($instance);

        foreach ($ref->getProperties() as $property) {
            foreach ($this->getAnnotationReader()->getPropertyAnnotations($property) as $annotation) {
                if (!is_callable($annotation)) {
                    $class = get_class($annotation);
                    throw new \LogicException("Annotation: {$class} is not callable.");
                }

                $instance = $annotation($container, $instance, $property) ?? $instance;
            }
        }

        return $instance;
    }

    /**
     * normalizeClassName
     *
     * @param string $className
     *
     * @return  string
     *
     * @since  3.5.19
     */
    protected static function normalizeClassName(string $className): string
    {
        return strtolower(trim($className, '\\'));
    }
}
