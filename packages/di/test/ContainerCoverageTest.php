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
use Windwalker\DI\MergeOptions;
use UnexpectedValueException;

/**
 * Covers container definition cloning, cache behavior, tagged resolution, and related edge cases.
 * Remove when these Container behaviors are no longer part of the package and no tests depend on them.
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
    public function resolvesTaggedFactoriesAndConfiguredParameters(): void
    {
        $container = new Container();
        $container->set('tagged-service', static fn(string $tag): string => $tag, tag: 'green');
        $container->setParameters(['class' => ['path' => \stdClass::class]]);

        $resolved = $container->resolve(
            static fn(string $tag): object => (object) ['tag' => $tag],
            tag: 'blue',
        );

        self::assertSame('blue', $resolved->tag);
        self::assertSame('green', $container->resolve('tagged-service', tag: 'green'));
        self::assertInstanceOf(\stdClass::class, $container->resolveParam('class.path'));
    }

    #[Test]
    public function clearsOneCachedDefinitionOrAllCachedDefinitions(): void
    {
        $container = new Container();
        $factoryCalls = 0;
        $container->share('service', static function () use (&$factoryCalls): int {
            return ++$factoryCalls;
        });

        self::assertSame(1, $container->get('service'));

        $container->clearCache('service');

        self::assertSame(2, $container->get('service'));

        $container->clearCache();

        self::assertSame(3, $container->get('service'));
    }

    #[Test]
    public function isolatedParentDefinitionsAreResolvedIndependentlyInChildren(): void
    {
        $parent = new Container();
        $parent->share('isolated', static fn(): object => new \stdClass(), new DIOptions(isolation: true));
        $parentInstance = $parent->get('isolated');

        $child = new Container($parent);
        $childInstance = $child->get('isolated');

        self::assertNotSame($parentInstance, $childInstance);
        self::assertSame($childInstance, $child->get('isolated'));
    }

    #[Test]
    public function appliesParentAndTaggedExtensionsAndCanModifyExistingServices(): void
    {
        $parent = new Container();
        $parent->extend('service', static fn(string $value): string => $value . '-parent');
        $parent->set('service', static fn(): string => 'value');

        $child = new Container($parent);
        $child->extend('service', static fn(string $value): string => $value . '-child');

        self::assertSame('value-parent-child', $child->get('service'));

        $child->set('tagged-service', static fn(string $tag): string => $tag, tag: 'green');
        $child->extend(
            'tagged-service',
            static fn(string $value): string => $value . '-extended',
            'green'
        );

        self::assertSame('green-extended', $child->get('tagged-service', tag: 'green'));

        $child->set('cached-service', 'before');
        self::assertSame('before', $child->get('cached-service'));
        $child->extend('cached-service', static fn(string $value): string => $value . '-after');

        self::assertSame('before-after', $child->get('cached-service'));

        $this->expectException(UnexpectedValueException::class);
        $child->modify('missing-service', static fn(mixed $value): mixed => $value);
    }

    #[Test]
    public function mergesParameterTreesWithRecursiveOverrideOptions(): void
    {
        $container = new Container();
        $container->setParameters([
            'settings' => [
                'keep' => true,
                'value' => 'original',
                'nested' => ['left' => 1],
            ],
            'root' => 'original',
        ]);

        $container->mergeParameters(
            'settings',
            ['value' => 'updated', 'nested' => ['right' => 2]],
            new MergeOptions(recursive: true, override: true),
        );

        self::assertSame(
            [
                'value' => 'updated',
                'nested' => ['right' => 2, 'left' => 1],
                'keep' => true,
            ],
            $container->getParam('settings'),
        );

        $container->mergeParameters(
            'settings',
            ['value' => 'ignored', 'nested' => ['right' => 3]],
            new MergeOptions(recursive: true),
        );
        $container->mergeParameters(null, ['root' => 'incoming', 'added' => true]);

        self::assertSame('updated', $container->getParam('settings.value'));
        self::assertSame(['right' => 2, 'left' => 1], $container->getParam('settings.nested'));
        self::assertSame('original', $container->getParam('root'));
        self::assertTrue($container->getParam('added'));
    }

    #[Test]
    public function normalizesAndCopiesContainerOptions(): void
    {
        $container = new Container();
        $options = new DIOptions(autowire: false);

        $container->setOptions($options);
        $options->autowire = true;

        self::assertFalse($container->getOptions()->autowire);

        $container->setOptions(Container::AUTO_WIRE);

        self::assertTrue($container->getOptions()->autowire);
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

        self::assertTrue(isset($container['temporary']));
        self::assertSame('value', $container['temporary']);

        unset($container['temporary']);

        self::assertFalse(isset($container['temporary']));
        self::assertFalse($container->has('temporary'));
    }
}
