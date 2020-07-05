<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\DI\Annotation;

use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Annotations\AnnotationReader;
use PhpDocReader\AnnotationException;
use PhpDocReader\PhpDocReader;
use Windwalker\DI\Container;
use Windwalker\DI\Exception\DependencyResolutionException;

/**
 * The Inject class.
 *
 * @Annotation
 *
 * @Target({"PROPERTY"})
 *
 * @since  3.4.4
 */
class Inject extends AbstractAnnotation implements PropertyAnnotationInterface
{
    /**
     * @var PhpDocReader
     */
    protected static $docReader;

    /**
     * getInjectable
     *
     * @param Container $container
     * @param string    $class
     *
     * @return  mixed
     *
     * @throws \ReflectionException
     * @throws \Windwalker\DI\Exception\DependencyResolutionException
     *
     * @since  3.4.4
     */
    public function resolveInjectable(Container $container, $class)
    {
        $id = $this->getOption('key') ?? $class;

        if ($container->has($id)) {
            return $container->get($id, (bool) $this->getOption('new'));
        }

        if (!class_exists($id)) {
            throw new DependencyResolutionException(
                sprintf('Class: "%s" not exists.', $id)
            );
        }

        return $container->newInstance($id);
    }

    /**
     * @inheritDoc
     * @throws DependencyResolutionException|\ReflectionException
     */
    public function __invoke(Container $container, $instance, \ReflectionProperty $property)
    {
        if (!class_exists(PhpDocReader::class)) {
            return $instance;
        }

        if (!$property instanceof \ReflectionProperty) {
            return $instance;
        }

        try {
            $varClass = $this->getDocReader()->getPropertyClass($property);
        } catch (AnnotationException $e) {
            throw new DependencyResolutionException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        if ($property->isProtected() || $property->isPrivate()) {
            $property->setAccessible(true);
        }

        $property->setValue(
            $instance,
            $this->resolveInjectable($container, $varClass)
        );

        if ($property->isProtected() || $property->isPrivate()) {
            $property->setAccessible(false);
        }

        return $instance;
    }

    /**
     * getDocReader
     *
     * @return  PhpDocReader
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function getDocReader(): PhpDocReader
    {
        if (!static::$docReader) {
            static::$docReader = new PhpDocReader();
        }

        return static::$docReader;
    }
}
