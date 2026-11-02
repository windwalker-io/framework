<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DI\Attributes\AttributeType;
use Windwalker\DI\Attributes\Decorator;
use Windwalker\DI\Attributes\Factory;
use Windwalker\DI\Attributes\Setup;
use Windwalker\DI\Container;
use Windwalker\DI\ContainerAwareTrait;
use Windwalker\DI\DICreateTrait;
use Windwalker\DI\PublicVisibleContainerAwareTrait;
use Windwalker\DI\ServiceAwareTrait;
use Windwalker\DI\BootableProviderInterface;
use Windwalker\DI\ServiceProviderInterface;

/**
 * Exercises DI traits and container integration behaviors that are not covered by the core container tests.
 */
class PackageComponentsTest extends TestCase
{
    #[Test]
    public function containerAwareTraitsLazilyCreateOrUseTheAssignedContainer(): void
    {
        $aware = new ContainerAwareFixture();
        $first = $aware->getContainer();

        self::assertInstanceOf(Container::class, $first);
        self::assertSame($first, $aware->getContainer());

        $replacement = new Container();
        $aware->setContainer($replacement);

        self::assertSame($replacement, $aware->getContainer());
        $aware->setContainer(null);

        self::assertNotSame($replacement, $aware->getContainer());

        $publicAware = new PublicContainerAwareFixture();
        $publicContainer = $publicAware->getContainer();
        $publicAware->setContainer($replacement);
        self::assertSame($replacement, $publicAware->container);

        self::assertInstanceOf(Container::class, $publicContainer);
        self::assertSame($replacement, $publicAware->getContainer());
    }

    #[Test]
    public function serviceAwareTraitDelegatesCreationResolutionAndInvocation(): void
    {
        $services = new ServiceAwareFixture();
        $container = new Container();
        $services->setContainer($container);

        $container->set('configured-value', 'resolved');
        $first = $services->service(TraitServiceObject::class, ['name' => 'shared']);
        $second = $services->service(TraitServiceObject::class, ['name' => 'ignored']);
        $made = $services->make(TraitServiceObject::class, ['name' => 'transient']);

        self::assertSame('shared', $first->name);
        self::assertSame($first, $second);
        self::assertNotSame($first, $made);
        self::assertSame('resolved', $services->resolve('configured-value'));
        self::assertSame('called', $services->call(static fn(string $value): string => $value, ['value' => 'called']));

        $services->bind('bound-value', static fn(): string => 'bound');

        self::assertSame('bound', $container->get('bound-value'));
    }

    #[Test]
    public function createTraitBuildsAResolvableObjectDefinition(): void
    {
        $definition = CreateTraitFixture::di(name: 'from-builder');
        $object = new Container()->resolve($definition);

        self::assertInstanceOf(TraitServiceObject::class, $object);
        self::assertSame('from-builder', $object->name);
    }

    #[Test]
    public function configRegistrationAppliesBindingsAliasesAttributesAndProvidersOnce(): void
    {
        $container = new Container();
        $providers = [];

        $container->registerByConfig(
            [
                'attributes' => [
                    Decorator::class => AttributeType::CLASSES,
                ],
                'bindings' => [
                    'config.value' => static fn(): string => 'from-config',
                ],
                'aliases' => [
                    'config.alias' => 'config.value',
                ],
                'providers' => [
                    ConfigCoverageProvider::class,
                    ConfigCoverageProvider::class,
                ],
            ],
            $providers
        );

        self::assertSame('from-config', $container->get('config.alias'));
        self::assertTrue($container->getAttributesResolver()->hasAttribute(Decorator::class, AttributeType::CLASSES));
        self::assertCount(1, $providers);
        self::assertSame('registered', $container->get('provider.registered'));
        self::assertSame('booted', $container->get('provider.booted'));
    }

    #[Test]
    public function configRegistrationLoadsPhpFilesAndAppliesNumericBindingsAndExtensions(): void
    {
        $container = new Container();
        $configFile = tempnam(sys_get_temp_dir(), 'windwalker-di-config-');

        if ($configFile === false) {
            self::fail('Unable to allocate a temporary config file.');
        }

        $config = <<<'PHP'
            <?php

            return [
                'attributes' => [
                    \Windwalker\DI\Attributes\Decorator::class,
                    static fn(\Windwalker\DI\Container $container) => $container
                        ->getAttributesResolver()
                        ->registerAttribute(
                            \Windwalker\DI\Attributes\Setup::class,
                            \Windwalker\DI\Attributes\AttributeType::METHODS
                        )
                ],
                'bindings' => [
                    \Windwalker\DI\Test\TraitServiceObject::class,
                ],
                'extends' => [
                    \Windwalker\DI\Test\TraitServiceObject::class => static function (
                        \Windwalker\DI\Test\TraitServiceObject $object
                    ): \Windwalker\DI\Test\TraitServiceObject {
                        $object->name = 'extended';

                        return $object;
                    }
                ]
            ];
            PHP;

        try {
            self::assertSame(strlen($config), file_put_contents($configFile, $config));
            $container->registerByConfig($configFile);
        } finally {
            if (is_file($configFile)) {
                unlink($configFile);
            }
        }

        self::assertTrue($container->getAttributesResolver()->hasAttribute(Decorator::class));
        self::assertTrue($container->getAttributesResolver()->hasAttribute(Setup::class, AttributeType::METHODS));
        self::assertSame('extended', $container->get(TraitServiceObject::class)->name);
    }

    #[Test]
    public function configRegistrationRejectsNonStringNumericBindings(): void
    {
        $this->expectException(\Windwalker\DI\Exception\DefinitionException::class);
        $this->expectExceptionMessageIsOrContains('Binding classes must with a string key');

        new Container()->registerByConfig([
            'bindings' => [
                new \stdClass()
            ]
        ]);
    }

    #[Test]
    public function setupAttributeRunsWhenContainerCreatesAnObject(): void
    {
        $container = new Container();
        $container->registerDefaultAttributes();

        $object = $container->newInstance(SetupAttributeFixture::class);

        self::assertTrue($object->isSetup);
    }

    #[Test]
    public function factoryAttributeResolvesTheReturnedClosure(): void
    {
        $container = new Container();

        $object = $container->newInstance(
            static fn() => #[Factory]
            static fn(): TraitServiceObject => new TraitServiceObject('from-factory')
        );

        self::assertInstanceOf(TraitServiceObject::class, $object);
        self::assertSame('from-factory', $object->name);
    }
}

/**
 * Uses ContainerAwareTrait to verify lazy container initialization and explicit replacement.
 * Remove when those trait behaviors are no longer covered.
 */
class ContainerAwareFixture
{
    use ContainerAwareTrait;
}

/**
 * Uses PublicVisibleContainerAwareTrait to verify its public container property and lazy fallback.
 * Remove when those trait behaviors are no longer covered.
 */
class PublicContainerAwareFixture
{
    use PublicVisibleContainerAwareTrait;
}

/**
 * Uses ServiceAwareTrait as a host for testing its public service delegation methods.
 * Remove when those trait behaviors are no longer covered.
 */
class ServiceAwareFixture
{
    use ServiceAwareTrait;
}

/**
 * Receives constructor arguments from ServiceAwareTrait and DICreateTrait behavior tests.
 * Remove when those trait behaviors are no longer covered.
 */
class TraitServiceObject
{
    public function __construct(public string $name = 'default') {}
}

/**
 * Uses DICreateTrait to expose the static definition builder in a concrete class.
 * Remove when DICreateTrait behavior is no longer covered.
 */
class CreateTraitFixture extends TraitServiceObject
{
    use DICreateTrait;
}

/**
 * Registers one service and boot action to validate config provider ordering and de-duplication.
 * Remove when ConfigRegisterTrait provider handling is no longer covered.
 */
class ConfigCoverageProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $container): void
    {
        $container->set('provider.registered', 'registered');
    }

    public function boot(Container $container): void
    {
        $container->set('provider.booted', 'booted');
    }
}

/**
 * Supplies a Setup method whose side effect verifies method-attribute execution during DI creation.
 * Remove when Setup attribute behavior is no longer covered.
 */
class SetupAttributeFixture
{
    public bool $isSetup = false;

    #[Setup]
    public function setup(): void
    {
        $this->isSetup = true;
    }
}
