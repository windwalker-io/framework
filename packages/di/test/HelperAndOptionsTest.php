<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Stringable;
use Windwalker\DI\Container;
use Windwalker\DI\Definition\DefinitionFactory;
use Windwalker\DI\Definition\ObjectBuilderDefinition;
use Windwalker\DI\Definition\StoreDefinition;
use Windwalker\DI\DIOptions;
use Windwalker\DI\MergeOptions;
use Windwalker\DI\Wrapper\CallbackWrapper;

use function Windwalker\DI\callback;
use function Windwalker\DI\create;
use function Windwalker\DI\prepare;
use function Windwalker\DI\prepare_shared;
use function Windwalker\DI\share;

/**
 * Covers DI option conversion, helper functions, callback wrappers, and the deprecated definition factory.
 */
class HelperAndOptionsTest extends TestCase
{
    #[Test]
    public function wrapsAndMergesDiOptionsWithoutMutatingTheInput(): void
    {
        $source = new DIOptions(shared: true, lazy: false);
        $copy = DIOptions::wrap($source);
        $copy->merge(['shared' => null, 'lazy' => true], ignoreNulls: true);

        self::assertNotSame($source, $copy);
        self::assertTrue($copy->shared);
        self::assertTrue($copy->lazy);
        self::assertFalse($source->lazy);

        $wrapped = DIOptions::wrap(Container::SHARED | Container::PROTECTED | Container::AUTO_WIRE);
        $record = DIOptions::wrap(['ignoreAttributes' => true]);

        self::assertTrue($wrapped->shared);
        self::assertTrue($wrapped->protected);
        self::assertTrue($wrapped->autowire);
        self::assertNull($wrapped->isolation);
        self::assertTrue($record->ignoreAttributes);
    }

    #[Test]
    public function wrapsMergeOptionsFromBitFlagsAndRecords(): void
    {
        $wrapped = MergeOptions::wrap(Container::MERGE_RECURSIVE | Container::MERGE_OVERRIDE);
        $record = MergeOptions::wrap(['recursive' => true]);

        self::assertTrue($wrapped->recursive);
        self::assertTrue($wrapped->override);
        self::assertTrue($record->recursive);
        self::assertFalse($record->override);
    }

    #[Test]
    public function helperFunctionsBuildDefinitionsAndConfigureSharedServices(): void
    {
        $definition = create(TraitServiceObject::class, name: 'created');
        $shared = share(TraitServiceObject::class, name: 'shared');
        $prepared = prepare(
            TraitServiceObject::class,
            static function (TraitServiceObject $object): TraitServiceObject {
                $object->name .= '-prepared';

                return $object;
            }
        );
        $preparedShared = prepare_shared(
            TraitServiceObject::class,
            static fn(TraitServiceObject $object): TraitServiceObject => $object
        );

        self::assertSame('created', (new Container())->resolve($definition)->name);
        self::assertTrue($shared->isShared());
        self::assertSame('shared', (new Container())->resolve($shared)->name);
        self::assertSame('default-prepared', (new Container())->resolve($prepared)->name);
        self::assertTrue($preparedShared->isShared());
    }

    #[Test]
    public function callbackWrapperCarriesContextAndOptionsIntoContainerExecution(): void
    {
        $context = new CallbackContextFixture();
        $container = new Container();
        $wrapper = callback(
            static fn(string $name): string => $name,
            $context,
            Container::AUTO_WIRE
        );

        self::assertInstanceOf(CallbackWrapper::class, $wrapper);
        self::assertSame($context, $wrapper->context);
        self::assertSame(Container::AUTO_WIRE, $wrapper->options);
        self::assertSame($wrapper->callable, $wrapper());
        self::assertSame('context-value', $container->execute($wrapper, 'context-value'));
    }

    #[Test]
    public function deprecatedDefinitionFactoryWrapsDefinitionsAndNormalizesClassNames(): void
    {
        $created = DefinitionFactory::create('value', Container::SHARED);
        $existing = new StoreDefinition('existing', 'value');
        $wrapped = DefinitionFactory::wrap($existing);
        $wrappedValue = DefinitionFactory::wrap('wrapped-value');
        $builder = new ObjectBuilderDefinition(TraitServiceObject::class);

        self::assertTrue($created->isShared());
        self::assertSame($existing, $wrapped);
        self::assertSame('wrapped-value', (new Container())->resolve($wrappedValue));
        self::assertSame(TraitServiceObject::class, DefinitionFactory::getClassName($builder));
        self::assertTrue(DefinitionFactory::isSameClass('\\' . TraitServiceObject::class, TraitServiceObject::class));
        self::assertFalse(DefinitionFactory::isSameClass(TraitServiceObject::class, ClassNameStringable::class));
        self::assertSame(TraitServiceObject::class, DefinitionFactory::getClassName(new TraitServiceObject()));
        self::assertSame(ClassNameStringable::class, DefinitionFactory::getClassName(new ClassNameStringable()));

        $closure = static fn(): string => 'value';

        self::assertSame(spl_object_hash($closure), DefinitionFactory::getClassName($closure));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Invalid object type');

        DefinitionFactory::getClassName([]);
    }
}

/**
 * Provides a stringable class-name value for DefinitionFactory conversion behavior.
 * Remove when that conversion is no longer covered.
 */
class ClassNameStringable implements Stringable
{
    public function __toString(): string
    {
        return 'stringable-name';
    }
}

/**
 * Provides an object context for callback wrapper execution tests.
 * Remove when callback wrapper context handling is no longer covered.
 */
class CallbackContextFixture {}
