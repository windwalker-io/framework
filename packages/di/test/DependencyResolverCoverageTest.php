<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use Windwalker\DI\Attributes\Inject;
use Windwalker\DI\Attributes\Lazy;
use Windwalker\DI\Attributes\NoAutowire;
use Windwalker\DI\Container;
use Windwalker\DI\DIOptions;
use Windwalker\DI\Definition\ObjectBuilderDefinition;
use Windwalker\DI\DependencyResolver;
use Windwalker\DI\Exception\DependencyResolutionException;
use Windwalker\DI\Test\Stub\StubInstantService;

use function Windwalker\raw;
use function Windwalker\ref;

/**
 * Covers dependency resolver attribute handling, lazy decisions, and parameter values.
 */
class DependencyResolverCoverageTest extends TestCase
{
    #[Test]
    public function resolvesInjectAttributesOnCallableParameters(): void
    {
        $container = new Container();
        $container->registerDefaultAttributes();

        $service = $container->call(
            static fn(#[Inject] StubInstantService $service): StubInstantService => $service
        );

        self::assertInstanceOf(StubInstantService::class, $service);
    }

    #[Test]
    public function noAutowireAttributePreventsAutomaticDependencyCreation(): void
    {
        $container = new Container(options: new DIOptions(autowire: true));

        $this->expectException(DependencyResolutionException::class);
        $this->expectExceptionMessageIsOrContains('Cannot resolve argument');

        $container->call(
            static fn(#[NoAutowire] ResolverDependency $dependency): ResolverDependency => $dependency
        );
    }

    #[Test]
    public function detectsLazyAttributesAndExplicitLazyOptions(): void
    {
        $resolver = (new Container())->getDependencyResolver();

        self::assertTrue($resolver->canLazy(new ReflectionClass(LazyResolverDependency::class), new DIOptions()));
        self::assertFalse($resolver->canLazy(new ReflectionClass(ResolverDependency::class), new DIOptions()));
        self::assertTrue($resolver->canLazy(new ReflectionClass(ResolverDependency::class), new DIOptions(lazy: true)));
    }

    #[Test]
    public function identifiesInternalClassesAndRejectsOtherReflectors(): void
    {
        self::assertTrue(DependencyResolver::isInternal(\Exception::class));
        self::assertTrue(DependencyResolver::isInternal(new ReflectionClass(InternalExceptionChild::class)));
        self::assertFalse(DependencyResolver::isInternal(ResolverDependency::class));
        self::assertFalse(
            DependencyResolver::isInternal(
                new ReflectionFunction('strlen')
            )
        );
    }

    #[Test]
    public function resolvesRawValuesParentReferencesAndObjectBuilders(): void
    {
        $parent = new Container();
        $parent->getParameters()->set('inherited', 'from-parent');
        $container = new Container($parent);
        $resolver = $container->getDependencyResolver();

        $rawValue = raw('literal');
        $reference = ref('inherited');
        $builder = new ObjectBuilderDefinition(ResolverBuiltDependency::class);

        self::assertSame('literal', $resolver->resolveParameterValue($rawValue));
        self::assertSame('from-parent', $resolver->resolveParameterValue($reference));
        self::assertInstanceOf(
            ResolverBuiltDependency::class,
            $resolver->resolveParameterValue($builder)
        );
    }
}

/**
 * A simple autowireable dependency intentionally not registered in the test container.
 * Remove when automatic dependency failure handling is no longer tested.
 */
class ResolverDependency {}

/**
 * Marks a dependency as lazy for canLazy behavior.
 * Remove when lazy detection is no longer tested.
 */
#[Lazy]
class LazyResolverDependency {}

/**
 * Extends an internal class to cover inherited internal-class detection.
 * Remove when inherited internal-class detection is no longer tested.
 */
class InternalExceptionChild extends \Exception {}

/**
 * Provides an object for builder resolution in the dependency value test.
 * Remove when builder value resolution is no longer tested.
 */
class ResolverBuiltDependency {}
