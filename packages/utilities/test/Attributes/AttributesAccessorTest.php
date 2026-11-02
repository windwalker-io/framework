<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Attributes;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Windwalker\Utilities\Attributes\AttributesAccessor;
use Windwalker\Utilities\Test\Stub\Attributes\StubMarkedChild;
use Windwalker\Utilities\Test\Stub\Attributes\StubMarkedEnum;
use Windwalker\Utilities\Test\Stub\Attributes\StubMarkedParent;
use Windwalker\Utilities\Test\Stub\Attributes\StubMarker;
use Windwalker\Utilities\Test\Stub\Attributes\StubSubMarker;

class AttributesAccessorTest extends TestCase
{
    #[Test]
    public function getAttributesFromAnyIncludesParentClassAttributes(): void
    {
        $attrs = AttributesAccessor::getAttributesFromAny(StubMarkedChild::class, StubMarker::class);

        self::assertSame(
            ['parent'],
            array_map(fn(ReflectionAttribute $attr) => $attr->newInstance()->name, $attrs)
        );

        $attrs = AttributesAccessor::getAttributesFromAny(
            StubMarkedChild::class,
            StubMarker::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        self::assertSame(
            ['child', 'parent'],
            array_map(fn(ReflectionAttribute $attr) => $attr->newInstance()->name, $attrs)
        );
    }

    #[Test]
    public function getAttributesFromAnyAcceptsObjectsAndReflectors(): void
    {
        $byObject = AttributesAccessor::getAttributesFromAny(new StubMarkedParent(), StubMarker::class);
        $byReflector = AttributesAccessor::getAttributesFromAny(
            new ReflectionProperty(StubMarkedParent::class, 'marked'),
            StubMarker::class
        );

        self::assertCount(1, $byObject);
        self::assertSame('parent', $byObject[0]->newInstance()->name);
        self::assertSame('prop', $byReflector[0]->newInstance()->name);
    }

    #[Test]
    public function getFirstAttributeReturnsFirstMatchOrNull(): void
    {
        $attr = AttributesAccessor::getFirstAttribute(StubMarkedParent::class, StubMarker::class);

        self::assertInstanceOf(ReflectionAttribute::class, $attr);
        self::assertSame(StubMarker::class, $attr->getName());
        self::assertNull(AttributesAccessor::getFirstAttribute(StubMarkedParent::class, StubSubMarker::class));
    }

    #[Test]
    public function getFirstAttributeInstanceReturnsInstanceOrNull(): void
    {
        $instance = AttributesAccessor::getFirstAttributeInstance(StubMarkedParent::class, StubMarker::class);

        self::assertInstanceOf(StubMarker::class, $instance);
        self::assertSame('parent', $instance->name);
        self::assertNull(
            AttributesAccessor::getFirstAttributeInstance(StubMarkedParent::class, StubSubMarker::class)
        );
    }

    #[Test]
    public function runAttributeIfExistsCallsHandlerWithInstanceAndSource(): void
    {
        $calls = [];

        $count = AttributesAccessor::runAttributeIfExists(
            StubMarkedParent::class,
            StubMarker::class,
            function (StubMarker $attr, mixed $source) use (&$calls) {
                $calls[] = [$attr->name, $source];
            }
        );

        self::assertSame(1, $count);
        self::assertSame([['parent', StubMarkedParent::class]], $calls);
    }

    #[Test]
    public function runAttributeIfExistsMatchesNamesCaseInsensitively(): void
    {
        $count = AttributesAccessor::runAttributeIfExists(
            StubMarkedParent::class,
            strtoupper(StubMarker::class),
            fn() => null
        );

        self::assertSame(1, $count);
    }

    #[Test]
    public function runAttributeIfExistsAcceptsPreparedAttributeList(): void
    {
        $attrs = new ReflectionClass(StubMarkedChild::class)->getAttributes();
        $names = [];

        $count = AttributesAccessor::runAttributeIfExists(
            $attrs,
            StubMarker::class,
            function (StubMarker $attr) use (&$names) {
                $names[] = $attr->name;
            },
            ReflectionAttribute::IS_INSTANCEOF
        );

        self::assertSame(1, $count);
        self::assertSame(['child'], $names);

        self::assertSame(0, AttributesAccessor::runAttributeIfExists($attrs, StubMarker::class, fn() => null));
    }

    #[Test]
    public function runAttributeIfExistsSupportsInstanceOfFlag(): void
    {
        $count = AttributesAccessor::runAttributeIfExists(
            StubMarkedChild::class,
            StubMarker::class,
            fn() => null,
            ReflectionAttribute::IS_INSTANCEOF
        );

        self::assertSame(2, $count);
    }

    #[Test]
    public function getMembersWithAttributeFindsConstantsPropertiesAndMethods(): void
    {
        $members = iterator_to_array(
            AttributesAccessor::getMembersWithAttribute(
                StubMarkedParent::class,
                StubMarker::class,
                ReflectionAttribute::IS_INSTANCEOF
            )
        );

        self::assertSame(['MARKED', 'marked'], array_keys($members));
        self::assertInstanceOf(\ReflectionClassConstant::class, $members['MARKED'][0]);
        self::assertSame('const', $members['MARKED'][1]->newInstance()->name);
    }

    #[Test]
    public function getMembersWithAttributeFiltersByMemberClassName(): void
    {
        $members = iterator_to_array(
            AttributesAccessor::getMembersWithAttribute(
                StubMarkedParent::class,
                StubMarker::class,
                ReflectionAttribute::IS_INSTANCEOF,
                ReflectionMethod::class
            )
        );

        self::assertSame(['marked'], array_keys($members));
        self::assertInstanceOf(ReflectionMethod::class, $members['marked'][0]);
    }

    #[Test]
    public function getMembersWithAttributeFiltersByClosure(): void
    {
        $members = iterator_to_array(
            AttributesAccessor::getMembersWithAttribute(
                new StubMarkedParent(),
                StubMarker::class,
                ReflectionAttribute::IS_INSTANCEOF,
                fn($member, ReflectionAttribute $attr) => $attr->newInstance()->name === 'prop'
            )
        );

        self::assertSame(['marked'], array_keys($members));
        self::assertInstanceOf(ReflectionProperty::class, $members['marked'][0]);
    }

    #[Test]
    public function getMembersWithAttributeFindsEnumCases(): void
    {
        $members = iterator_to_array(
            AttributesAccessor::getMembersWithAttribute(StubMarkedEnum::class, StubMarker::class)
        );

        self::assertSame(['One'], array_keys($members));
        self::assertInstanceOf(\ReflectionEnumUnitCase::class, $members['One'][0]);
        self::assertSame('case', $members['One'][1]->newInstance()->name);

        $byEnumRef = iterator_to_array(
            AttributesAccessor::getMembersWithAttribute(new \ReflectionEnum(StubMarkedEnum::class), StubMarker::class)
        );

        self::assertSame(['One'], array_keys($byEnumRef));
    }

    #[Test]
    public function getFirstMemberWithAttributeReturnsFirstOrNull(): void
    {
        $first = AttributesAccessor::getFirstMemberWithAttribute(
            StubMarkedParent::class,
            StubMarker::class,
            ReflectionAttribute::IS_INSTANCEOF,
            ReflectionProperty::class
        );

        self::assertSame('marked', $first[0]->getName());
        self::assertInstanceOf(ReflectionProperty::class, $first[0]);
        self::assertNull(
            AttributesAccessor::getFirstMemberWithAttribute(\stdClass::class, StubMarker::class)
        );
    }

    #[Test]
    public function scanDirAndRunAttributesRunsHandlerForMatchingClassesRecursively(): void
    {
        $handled = [];

        $results = AttributesAccessor::scanDirAndRunAttributes(
            StubMarker::class,
            __DIR__ . '/../Stub/AttributeScan',
            'Windwalker\\Utilities\\Test\\Stub\\AttributeScan',
            function (StubMarker $attr, string $class, \SplFileInfo $file) use (&$handled) {
                $handled[$attr->name] = [$class, $file->getBasename()];
            }
        );

        $ns = 'Windwalker\\Utilities\\Test\\Stub\\AttributeScan\\';

        self::assertSame(
            [
                $ns . 'Alpha' => 1,
                $ns . 'Plain' => 0,
                $ns . 'Nested\\Beta' => 1,
            ],
            $this->sortByKeyIfNeeded($results, [$ns . 'Alpha', $ns . 'Plain', $ns . 'Nested\\Beta'])
        );
        self::assertSame([$ns . 'Alpha', 'Alpha.php'], $handled['alpha']);
        self::assertSame([$ns . 'Nested\\Beta', 'Beta.php'], $handled['beta']);
    }

    private function sortByKeyIfNeeded(array $results, array $order): array
    {
        $sorted = [];

        foreach ($order as $key) {
            $sorted[$key] = $results[$key];
        }

        self::assertCount(count($order), $results);

        return $sorted;
    }
}
