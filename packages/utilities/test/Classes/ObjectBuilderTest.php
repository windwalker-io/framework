<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Classes;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Classes\ObjectBuilder;
use Windwalker\Utilities\Classes\ObjectBuilderAwareTrait;
use Windwalker\Utilities\Classes\PreventInitialTrait;
use Windwalker\Utilities\Classes\ClassDecorator;

class ObjectBuilderTest extends TestCase
{
    #[Test]
    public function createObjectInstantiatesClassWithArguments(): void
    {
        $o = (new ObjectBuilder())->createObject(\ArrayObject::class, ['a' => 1]);

        self::assertInstanceOf(\ArrayObject::class, $o);
        self::assertSame(['a' => 1], $o->getArrayCopy());
    }

    #[Test]
    public function createObjectUsesCustomBuilder(): void
    {
        $builder = (new ObjectBuilder())->setBuilder(fn(string $class, ...$args) => new \stdClass());

        self::assertInstanceOf(\stdClass::class, $builder->createObject(\ArrayObject::class));
    }

    #[Test]
    public function getBuilderCachesDefaultBuilder(): void
    {
        $builder = new ObjectBuilder();

        self::assertSame($builder->getBuilder(), $builder->getBuilder());
    }

    #[Test]
    public function awareTraitCreatesObjectBuilderLazily(): void
    {
        $o = new class () {
            use ObjectBuilderAwareTrait;
        };

        $builder = $o->getObjectBuilder();

        self::assertInstanceOf(ObjectBuilder::class, $builder);
        self::assertSame($builder, $o->getObjectBuilder());
    }

    #[Test]
    public function preventInitialTraitBlocksPublicInstantiation(): void
    {
        $class = new \ReflectionClass(StubPreventInitial::class);

        self::assertTrue($class->getConstructor()->isProtected());
        self::assertInstanceOf(StubPreventInitial::class, StubPreventInitial::make());
    }

    #[Test]
    public function classDecoratorForwardsCallsToInnerObject(): void
    {
        $decorator = new ClassDecorator(new \ArrayObject([1, 2, 3]));

        self::assertSame(3, $decorator->count());
    }
}

/**
 * Fixture using PreventInitialTrait, used by ObjectBuilderTest.
 *
 * Remove it together with the PreventInitialTrait test when the trait is removed.
 */
class StubPreventInitial
{
    use PreventInitialTrait;

    public static function make(): static
    {
        return new static();
    }
}
