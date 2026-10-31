<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DI\Container;
use Windwalker\DI\DIOptions;
use Windwalker\DI\Definition\StoreDefinition;
use Windwalker\DI\Exception\DependencyResolutionException;
use Windwalker\DI\Exception\DefinitionException;

/**
 * Covers container definition cloning, error propagation, value extraction, and cache reporting.
 */
class ContainerCoverageTest extends TestCase
{
    #[Test]
    public function setClonesDefinitionsAndAppliesTheNewIdTagAndProvidedLevel(): void
    {
        $container = new Container();
        $original = new StoreDefinition('original', 'value');

        $definition = $container->set(
            'service',
            $original,
            new DIOptions(providedIn: 1),
            'tagged'
        );

        self::assertNotSame($original, $definition);
        self::assertSame('service', $definition->getId());
        self::assertSame('value', $definition->resolve($container, tag: 'tagged'));
        self::assertTrue($definition->validateProvidedIn($container));
        self::assertFalse($definition->validateProvidedIn(new Container($container)));
        self::assertSame('original', $original->getId());
    }

    #[Test]
    public function wrapsContainerExceptionsRaisedWhileResolvingDefinitions(): void
    {
        $container = new Container();
        $container->set('broken', static function (): never {
            throw new DefinitionException('factory-failed');
        });

        $this->expectException(DependencyResolutionException::class);
        $this->expectExceptionMessageIsOrContains('Error when resolving broken');

        $container->get('broken');
    }

    #[Test]
    public function rejectsNullResolveSources(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('source');

        (new Container())->resolve(null);
    }

    #[Test]
    public function extractsClosuresButPreservesLiteralValues(): void
    {
        $container = new Container();

        self::assertSame('created', $container->extractValue(static fn(): string => 'created'));
        self::assertSame('literal', $container->extractValue('literal'));
    }

    #[Test]
    public function reportsOnlyResolvedDefinitionsInTheCacheDump(): void
    {
        $container = new Container();
        $container->share('resolved', static fn(): object => new \stdClass());
        $container->share('pending', static fn(): object => new \stdClass());
        $resolved = $container->get('resolved');

        $cached = $container->dumpCached();

        self::assertSame($resolved, $cached['resolved']);
        self::assertArrayNotHasKey('pending', $cached);
    }

    #[Test]
    public function arrayAccessCanRemoveRegisteredDefinitions(): void
    {
        $container = new Container();
        $container['temporary'] = 'value';

        unset($container['temporary']);

        self::assertFalse($container->has('temporary'));
    }
}
