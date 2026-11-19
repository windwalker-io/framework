<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DI\Attributes\Inject;
use Windwalker\DI\Attributes\Service;
use Windwalker\DI\Container;
use Windwalker\DI\Exception\DefinitionResolveException;
use Windwalker\DI\Exception\DependencyResolutionException;

interface RegressionNoImplInterface {}

class RegressionRequiredNoImpl
{
    #[Inject]
    public RegressionNoImplInterface $required;
}

class RegressionNullableNoImpl
{
    #[Inject]
    public ?RegressionNoImplInterface $maybe = null;
}

#[Service(providedIn: 3)]
class RegressionSharedLevelService {}

class ProvidedInRegressionTest extends TestCase
{
    #[Test]
    public function injectRequiredUnregisteredInterfaceThrowsDependencyException(): void
    {
        $container = new Container();
        $container->registerDefaultAttributes();

        $this->expectException(DefinitionResolveException::class);
        $this->expectExceptionMessage('Unable to inject object');

        $container->newInstance(RegressionRequiredNoImpl::class);
    }

    #[Test]
    public function injectNullableUnregisteredInterfaceFallsBackToNull(): void
    {
        $container = new Container();
        $container->registerDefaultAttributes();

        $obj = $container->newInstance(RegressionNullableNoImpl::class);

        self::assertNull($obj->maybe);
    }

    #[Test]
    public function sharedServiceRespectsProvidedInLevel(): void
    {
        $container = new Container();
        $container->registerDefaultAttributes();

        $this->expectException(DependencyResolutionException::class);

        $container->get(RegressionSharedLevelService::class);
    }
}
