<?php

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use Attribute;
use JetBrains\PhpStorm\Pure;
use Psr\Container\ContainerExceptionInterface;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;
use RuntimeException;
use Windwalker\DI\Container;
use Windwalker\DI\Exception\DefinitionResolveException;
use Windwalker\DI\Exception\DependencyResolutionException;

/**
 * The Inject class.
 *
 * @since  3.4.4
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Inject implements ContainerAttributeInterface
{
    /**
     * Inject constructor.
     *
     * @param  string|null  $id
     * @param  bool         $forceNew
     * @param  string|null  $tag
     */
    public function __construct(public ?string $id = null, public bool $forceNew = false, public ?string $tag = null)
    {
    }

    /**
     * @param  AttributeHandler  $handler
     *
     * @return mixed
     */
    #[Pure]
    public function __invoke(
        AttributeHandler $handler
    ): callable {
        /** @var ReflectionProperty|ReflectionParameter $reflector */
        $reflector = $handler->getReflector();

        return function (...$args) use ($handler, $reflector) {
            if ($reflector instanceof ReflectionParameter) {
                return $this->handleParameter($handler);
            }

            if ($handler->getObject() === null) {
                throw new RuntimeException('No target object to inject.');
            }

            $value = $this->resolveInjectable($handler->getContainer(), $reflector);

            $reflector->setValue($handler->getObject(), $value);

            return $value;
        };
    }

    /**
     * @throws \ReflectionException
     * @throws DependencyResolutionException
     */
    protected function handleParameter(AttributeHandler $handler): mixed
    {
        return $this->resolveInjectable($handler->getContainer(), $handler->getReflector());
    }

    /**
     * @throws DependencyResolutionException
     */
    protected function getTypeName(ReflectionProperty|ReflectionParameter $reflector): mixed
    {
        $type = $reflector->getType();

        if ($this->id) {
            $varClass = $this->id;
        } else {
            if ($type instanceof ReflectionUnionType) {
                $types = [$type->getTypes()];
            } else {
                $types = [$type];
            }

            $varClass = null;

            foreach ($types as $type) {
                if (
                    class_exists($type->getName())
                    || interface_exists($type->getName())
                    || enum_exists($type->getName())
                ) {
                    $varClass = $type->getName();
                    break;
                }
            }
        }

        if (!$varClass) {
            throw new DependencyResolutionException(
                sprintf('Unable to resolve injection of property: "%s".', $reflector->getName())
            );
        }

        return $varClass;
    }

    /**
     * @throws \ReflectionException
     * @throws DependencyResolutionException
     */
    public function resolveInjectable(Container $container, ReflectionProperty|ReflectionParameter $reflector): mixed
    {
        $id = $this->getTypeName($reflector);
        $tag = $this->tag;

        try {
            if ($container->has($id, $tag)) {
                return $container->get($id, $this->forceNew, $tag);
            }

            if (class_exists($id) || interface_exists($id)) {
                return $this->createObject($container, $id);
            }
        } catch (ContainerExceptionInterface $e) {
            $this->reportInjectingError($reflector, $id, $e);
            return null;
        }

        $this->reportInjectingError($reflector, $id);

        return null;
    }

    /**
     * @throws \ReflectionException
     * @throws DefinitionResolveException
     */
    protected function createObject(Container $container, string $id): object
    {
        return $container->newInstance($id);
    }

    /**
     * @param  ReflectionParameter|ReflectionProperty  $reflector
     * @param  mixed                                   $id
     * @param  \Throwable|null                         $e
     *
     * @return  void
     *
     * @throws DependencyResolutionException
     */
    protected function reportInjectingError(
        ReflectionParameter|ReflectionProperty $reflector,
        mixed $id,
        ?\Throwable $e = null
    ): void {
        if (!$reflector->getType()->allowsNull()) {
            $class = $reflector->getDeclaringClass();
            $member = $reflector->getName();

            throw new DependencyResolutionException(
                "Unable to inject object $id for class $class::$member" .
                ($e ? ' - ' . $e->getMessage() : ''),
                $e->getCode(),
                $e
            );
        }
    }
}
