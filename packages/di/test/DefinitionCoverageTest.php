<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use BadMethodCallException;
use Windwalker\DI\Attributes\Isolation;
use Windwalker\DI\Container;
use Windwalker\DI\DIOptions;
use Windwalker\DI\Definition\ObjectBuilderDefinition;
use Windwalker\DI\Definition\StoreDefinition;
use Windwalker\DI\Exception\DefinitionException;
use UnitEnum;

/**
 * Covers service definition constraints, caching, isolation, and object-builder behavior.
 */
class DefinitionCoverageTest extends TestCase
{
    #[Test]
    public function validatesProvidedInRulesForContainerLevels(): void
    {
        $root = new Container();
        $child = new Container($root);
        $rules = [
            1,
            [1],
            static fn(int $level): bool => $level === 1
        ];

        foreach ($rules as $rule) {
            $definition = new StoreDefinition('service', 'value');
            $definition->providedIn($rule);

            self::assertTrue($definition->validateProvidedIn($root));
            self::assertFalse($definition->validateProvidedIn($child));
        }

        self::assertTrue(new StoreDefinition('service', 'value')->validateProvidedIn($child));
    }

    #[Test]
    public function rejectsResolutionOutsideTheProvidedContainerLevels(): void
    {
        $root = new Container();
        $child = new Container($root);
        $definition = new StoreDefinition('service', static fn(): string => 'value');
        $definition->providedIn(1);

        self::assertSame('value', $definition->resolve($root));

        $this->expectException(DefinitionException::class);
        $definition->resolve($child);
    }

    #[Test]
    public function sharedDefinitionsCacheExtensionsAndClearTheCacheWhenReset(): void
    {
        $container = new Container();
        $factoryCalls = 0;
        $definition = new StoreDefinition(
            'service',
            static function () use (&$factoryCalls): int {
                return ++$factoryCalls;
            },
            new DIOptions(shared: true)
        );
        $definition->setContainer($container)
            ->extend(static fn(int $value): int => $value + 1);

        self::assertTrue($definition->isShared());
        self::assertSame(2, $definition->resolve());
        self::assertSame(2, $definition->resolve());
        self::assertSame(1, $factoryCalls);

        $definition->set(static function () use (&$factoryCalls): int {
            return ++$factoryCalls + 10;
        });

        self::assertSame(13, $definition->resolve());
        self::assertSame(2, $factoryCalls);
    }

    #[Test]
    public function isolationAttributeCanBeOverriddenByAnExplicitOption(): void
    {
        self::assertTrue(new StoreDefinition(IsolatedDefinitionTarget::class, null)->isIsolation());
        self::assertFalse(
            new StoreDefinition(IsolatedDefinitionTarget::class, null, new DIOptions(isolation: false))
                ->isIsolation()
        );
        self::assertFalse(new StoreDefinition(DefinitionBuildTarget::class, null)->isIsolation());
        self::assertTrue(
            new StoreDefinition(DefinitionBuildTarget::class, null, new DIOptions(isolation: true))
                ->isIsolation()
        );
    }

    #[Test]
    public function objectBuilderResolvesAndCachesArgumentsBeforeApplyingExtensions(): void
    {
        $container = new Container();
        $argumentCalls = 0;
        $builder = new ObjectBuilderDefinition(DefinitionBuildTarget::class);
        $builder->setArgument(
            'value',
            static function () use (&$argumentCalls): string {
                $argumentCalls++;

                return 'built';
            }
        );
        $builder->tag('builder-tag')
            ->extend(static function (DefinitionBuildTarget $target): DefinitionBuildTarget {
                $target->value .= '-extended';

                return $target;
            });

        $first = $builder->resolve($container);
        $second = $builder->resolve($container);

        self::assertSame('built-extended', $first->value);
        self::assertSame('builder-tag', $first->tag);
        self::assertNotSame($first, $second);
        self::assertSame(1, $argumentCalls);
    }

    #[Test]
    public function builderForksArgumentsAndResetClearsCachedState(): void
    {
        $container = new Container();
        $argumentCalls = 0;
        $builder = new ObjectBuilderDefinition(DefinitionBuildTarget::class);
        $builder->setArgument(
            'value',
            static function () use (&$argumentCalls): string {
                $argumentCalls++;

                return 'original';
            }
        );
        $builder->resolveArgument($container, 'value');

        $fork = $builder->fork(['value' => 'forked']);

        self::assertSame('original', $builder->resolveArgument($container, 'value'));
        self::assertSame('forked', $fork->resolveArgument($container, 'value'));
        self::assertSame(1, $argumentCalls);
        self::assertTrue($builder->hasArgument('value'));

        $builder->removeArgument('value');
        self::assertFalse($builder->hasArgument('value'));
        self::assertSame('fallback', $builder->resolveArgument($container, 'value', 'fallback'));

        $builder->setArgument('value', 'before-reset')
            ->extend(static fn(Container $container, DefinitionBuildTarget $target): DefinitionBuildTarget => $target);
        $builder->reset();

        self::assertSame([], $builder->getArguments());
        self::assertSame([], $builder->extends);
    }

    #[Test]
    public function magicBuilderMethodsCreateObjectsApplyOverridesAndRejectUnknownMethods(): void
    {
        $container = new Container();
        $builder = new ObjectBuilderDefinition(DefinitionBuildTarget::class);
        $builder->setArgument('value', 'default')
            ->extend(static function (Container $container, DefinitionBuildTarget $target): DefinitionBuildTarget {
                $target->value .= '-extended';

                return $target;
            });

        $target = $builder->createObject($container, ['value' => 'override']);

        self::assertSame('override-extended', $target->value);

        $this->expectException(BadMethodCallException::class);
        $builder->unknownOperation($container);
    }

    #[Test]
    public function cloningAStoreDefinitionKeepsItsObjectBuilderIndependent(): void
    {
        $container = new Container();
        $builder = new ObjectBuilderDefinition(DefinitionBuildTarget::class);
        $builder->setArgument('value', 'original');
        $definition = new StoreDefinition('service', $builder);
        $clone = clone $definition;
        $clone->value->setArgument('value', 'cloned');

        self::assertSame('original', $definition->resolve($container)->value);
        self::assertSame('cloned', $clone->resolve($container)->value);
    }

    #[Test]
    public function sharedDefinitionsKeepEnumTagsInDistinctCacheEntries(): void
    {
        $factoryCalls = 0;
        $definition = new StoreDefinition(
            'tagged-service',
            static function (UnitEnum|string|null $tag = null) use (&$factoryCalls): string {
                $factoryCalls++;

                return $tag instanceof UnitEnum ? $tag->name : (string) $tag;
            },
            new DIOptions(shared: true)
        );
        $definition->setContainer(new Container());

        foreach ([CoverageBackedTag::FIRST, CoverageUnitTag::SECOND] as $tag) {
            self::assertSame($tag->name, $definition->resolve(tag: $tag));
            self::assertSame($tag->name, $definition->resolve(tag: $tag));
        }

        self::assertSame(2, $factoryCalls);
    }
}

/**
 * Marks a definition target for testing Isolation attribute handling.
 * Remove when the isolation attribute behavior is no longer tested.
 */
#[Isolation]
class IsolatedDefinitionTarget {}

/**
 * Receives builder-resolved arguments and tags in the ObjectBuilderDefinition test.
 * Remove when those builder behaviors are no longer tested.
 */
class DefinitionBuildTarget
{
    public function __construct(
        public string $value,
        public ?string $tag = null
    ) {}
}

/**
 * Exercises backed-enum cache keys in StoreDefinition.
 * Remove when backed-enum cache behavior is no longer tested.
 */
enum CoverageBackedTag: string
{
    case FIRST = 'first';
}

/**
 * Exercises unit-enum cache keys in StoreDefinition.
 * Remove when unit-enum cache behavior is no longer tested.
 */
enum CoverageUnitTag
{
    case SECOND;
}
