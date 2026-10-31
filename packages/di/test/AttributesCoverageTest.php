<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Windwalker\DI\Attributes\Decorator;
use Windwalker\DI\Attributes\Inject;
use Windwalker\DI\Attributes\Service;
use Windwalker\DI\Container;
use Windwalker\DI\DIOptions;
use Windwalker\DI\Exception\DependencyResolutionException;
use Windwalker\DI\Test\Injection\Wrapped;
use Windwalker\DI\Test\Stub\StubInstantService;

/**
 * Covers injection attribute behavior, including typed resolution, tags, shared instances, and failure handling.
 */
class AttributesCoverageTest extends TestCase
{
    #[Test]
    public function injectsAClassFromAUnionTypedParameter(): void
    {
        $parameter = new ReflectionMethod(UnionInjectedService::class, '__construct')->getParameters()[0];

        $service = new Inject()->resolveInjectable(new Container(), $parameter);

        self::assertInstanceOf(StubInstantService::class, $service);
    }

    #[Test]
    public function resolvesAnExplicitIdUsingADynamicTag(): void
    {
        $container = new Container();
        $service = new StubInstantService();
        $container->set('service', $service, new DIOptions(shared: true), tag: 'selected');
        $resolvedId = null;
        $inject = new Inject(
            id: 'service',
            tag: static function (Container $container, string $id) use (&$resolvedId): string {
                $resolvedId = $id;

                return 'selected';
            }
        );

        $resolved = $inject->resolveInjectable($container, new ReflectionProperty(InjectableProperties::class, 'required'));

        self::assertSame($service, $resolved);
        self::assertSame('service', $resolvedId);
    }

    #[Test]
    public function forceNewBypassesTheCachedServiceInstance(): void
    {
        $container = new Container();
        $container->share('service', static fn() => new StubInstantService());
        $first = $container->get('service');

        $resolved = new Inject('service', true)->resolveInjectable(
            $container,
            new ReflectionProperty(InjectableProperties::class, 'required')
        );

        self::assertInstanceOf(StubInstantService::class, $resolved);
        self::assertNotSame($first, $resolved);
    }

    #[Test]
    public function unresolvedNullableInjectionReturnsNull(): void
    {
        $resolved = new Inject('missing-service')->resolveInjectable(
            new Container(),
            new ReflectionProperty(InjectableProperties::class, 'optional')
        );

        self::assertNull($resolved);
    }

    #[Test]
    public function unresolvedNullableInjectionReturnsNullWhenTheFactoryFails(): void
    {
        $container = new Container();
        $container->set('broken-service', static function (): never {
            throw new DependencyResolutionException('factory-failed');
        });

        $resolved = new Inject('broken-service')->resolveInjectable(
            $container,
            new ReflectionProperty(InjectableProperties::class, 'optional')
        );

        self::assertNull($resolved);
    }

    #[Test]
    public function unresolvedRequiredInjectionReportsTheTarget(): void
    {
        $this->expectException(DependencyResolutionException::class);
        $this->expectExceptionMessageIsOrContains('$required');

        new Inject('missing-service')->resolveInjectable(
            new Container(),
            new ReflectionProperty(InjectableProperties::class, 'required')
        );
    }

    #[Test]
    public function unresolvedRequiredParameterReportsItsPositionAndName(): void
    {
        $parameter = new ReflectionMethod(RequiredDependencyParameter::class, '__construct')->getParameters()[0];

        $this->expectException(DependencyResolutionException::class);
        $this->expectExceptionMessageIsOrContains('Argument #0');
        $this->expectExceptionMessageIsOrContains('$service');

        new Inject('missing-service')->resolveInjectable(new Container(), $parameter);
    }

    #[Test]
    public function rejectsInjectionWithoutAnExplicitIdForScalarTypes(): void
    {
        $this->expectException(DependencyResolutionException::class);
        $this->expectExceptionMessageIsOrContains('Unable to resolve injection');

        new Inject()->resolveInjectable(
            new Container(),
            new ReflectionProperty(InjectableProperties::class, 'scalar')
        );
    }

    #[Test]
    public function serviceInjectionCreatesAndReusesASharedObject(): void
    {
        $container = new Container();
        $service = new Service();
        $property = new ReflectionProperty(InjectableProperties::class, 'service');

        $first = $service->resolveInjectable($container, $property);
        $second = $service->resolveInjectable($container, $property);

        self::assertInstanceOf(StubInstantService::class, $first);
        self::assertSame($first, $second);
    }

    #[Test]
    public function serviceInjectionResolvesDynamicTagsForSharedObjects(): void
    {
        $container = new Container();
        $resolvedId = null;
        $service = new Service(
            tag: static function (Container $container, string $id) use (&$resolvedId): string {
                $resolvedId = $id;

                return 'dynamic';
            }
        );
        $property = new ReflectionProperty(InjectableProperties::class, 'service');

        $first = $service->resolveInjectable($container, $property);
        $second = $service->resolveInjectable($container, $property);

        self::assertSame(StubInstantService::class, $resolvedId);
        self::assertTrue($container->has(StubInstantService::class, 'dynamic'));
        self::assertSame($first, $second);
    }

    #[Test]
    public function serviceNameArgumentDoesNotEnableForceNew(): void
    {
        $service = new Service(forceNewOrService: 'legacy-service');

        self::assertSame('legacy-service', $service->name);
        self::assertFalse($service->forceNew);
    }

    #[Test]
    public function resolverCreatesObjectsThroughTheContainer(): void
    {
        $container = new Container();
        $resolver = $container->getAttributesResolver();

        $created = $resolver->createObject(ResolverCreatedObject::class, 'provided');

        self::assertInstanceOf(ResolverCreatedObject::class, $created);
        self::assertSame('provided', $created->value);
    }

    #[Test]
    public function resolverAdaptsClassBuildersAndDecoratesInstances(): void
    {
        $container = new Container();
        $resolver = $container->getAttributesResolver();
        $handler = $resolver->resolveClassCreate(ResolverCreatedObject::class);
        $arguments = ['provided'];
        $options = 0;
        $created = $handler($arguments, $options);

        $resolver->registerAttribute(Decorator::class);
        $instance = new DecoratableResolverObject();
        $decorated = $resolver->decorateObject($instance);

        self::assertInstanceOf(ResolverCreatedObject::class, $created);
        self::assertSame('provided', $created->value);
        self::assertInstanceOf(Wrapped::class, $decorated);
        self::assertSame($instance, $decorated->instance);
    }
}

/**
 * Provides a union-typed constructor parameter to verify class selection by Inject.
 * Remove when this behavior is no longer tested.
 */
class UnionInjectedService
{
    public function __construct(public StubInstantService|string $dependency) {}
}

/**
 * Provides required, nullable, and service-typed properties for Inject and Service behavior tests.
 * Remove when these injection cases are no longer tested.
 */
class InjectableProperties
{
    public object $required;

    public ?object $optional = null;

    public StubInstantService $service;

    public int $scalar;
}

/**
 * Provides a required constructor parameter for injection error reporting.
 * Remove when required-parameter error reporting is no longer tested.
 */
class RequiredDependencyParameter
{
    public function __construct(StubInstantService $service) {}
}

/**
 * Provides a constructor target for the resolver's container-backed creation and adapted builder tests.
 * Remove when those resolver behaviors are no longer tested.
 */
class ResolverCreatedObject
{
    public function __construct(public string $value = 'default') {}
}

/**
 * Carries a Decorator attribute to verify the DI resolver wraps an existing object.
 * Remove when resolver decoration is no longer tested.
 */
#[Decorator(Wrapped::class)]
class DecoratableResolverObject {}
