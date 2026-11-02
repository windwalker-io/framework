<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use ReflectionProperty;
use Windwalker\DI\Attributes\Service;
use Windwalker\DI\Container;
use Windwalker\DI\Test\Stub\StubInstantService;
use Windwalker\DI\Test\Stub\StubTransientService;
use Windwalker\DI\Test\Stub\StubTransientTaggedService;

class TransientServiceTest extends TestCase
{
    #[Test]
    public function classLevelNonSharedServiceCreatesNewInstanceEveryTime(): void
    {
        $container = new Container();

        $a = $container->get(StubTransientService::class);
        $b = $container->get(StubTransientService::class);

        self::assertInstanceOf(StubTransientService::class, $a);
        self::assertNotSame($a, $b);
    }

    #[Test]
    public function classLevelNonSharedServiceWorksWithTag(): void
    {
        $container = new Container();

        $a = $container->get(StubTransientTaggedService::class, tag: 'foo');
        $b = $container->get(StubTransientTaggedService::class, tag: 'foo');

        self::assertNotSame($a, $b);
    }

    #[Test]
    public function sharedIsDefaultForClassLevelService(): void
    {
        $container = new Container();

        self::assertSame(
            $container->get(StubInstantService::class),
            $container->get(StubInstantService::class),
        );
        self::assertTrue(new Service()->shared);
    }

    #[Test]
    public function parameterInjectionCreatesNewInstanceEveryTime(): void
    {
        $container = new Container();
        $service = new Service(shared: false);
        $parameter = new ReflectionParameter(
            static fn(StubInstantService $s) => $s,
            's',
        );

        $a = $service->resolveInjectable($container, $parameter);
        $b = $service->resolveInjectable($container, $parameter);

        self::assertInstanceOf(StubInstantService::class, $a);
        self::assertNotSame($a, $b);
        self::assertSame(
            $container->get(StubInstantService::class),
            $container->get(StubInstantService::class),
        );
    }

    #[Test]
    public function propertyInjectionCreatesNewInstanceEveryTime(): void
    {
        $container = new Container();
        $service = new Service(shared: false);
        $property = new ReflectionProperty(TransientHolder::class, 'service');

        self::assertNotSame(
            $service->resolveInjectable($container, $property),
            $service->resolveInjectable($container, $property),
        );
    }

    #[Test]
    public function sharedPropertyInjectionStillShares(): void
    {
        $container = new Container();
        $service = new Service();
        $property = new ReflectionProperty(TransientHolder::class, 'service');

        self::assertSame(
            $service->resolveInjectable($container, $property),
            $service->resolveInjectable($container, $property),
        );
    }
}

class TransientHolder
{
    public StubInstantService $service;
}
