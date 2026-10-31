<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Hydrator;

use ArrayIterator;
use ArrayObject;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Windwalker\Database\Hydrator\SimpleHydrator;
use Windwalker\Data\Collection;

class SimpleHydratorTest extends TestCase
{
    #[Test]
    public function extractsTraversableValues(): void
    {
        self::assertSame(
            ['name' => 'flower'],
            new SimpleHydrator()->extract(new ArrayIterator(['name' => 'flower']))
        );
    }

    #[Test]
    public function extractsObjectPropertiesIncludingNonPublicProperties(): void
    {
        $object = new class () {
            public string $name = 'flower';

            private int $id = 7;
        };

        self::assertSame(
            ['name' => 'flower', 'id' => 7],
            new SimpleHydrator()->extract($object)
        );
    }

    #[Test]
    public function hydratesAccessorAccessibleObjects(): void
    {
        $object = new Collection();

        $result = new SimpleHydrator()->hydrate(['name' => 'flower'], $object);

        self::assertSame($object, $result);
        self::assertSame('flower', $object->get('name'));
    }

    #[Test]
    public function hydratesArrayAccessObjects(): void
    {
        $object = new ArrayObject();

        $result = new SimpleHydrator()->hydrate(['name' => 'flower'], $object);

        self::assertSame($object, $result);
        self::assertSame('flower', $object['name']);
    }

    #[Test]
    public function hydratesStdClassObjects(): void
    {
        $object = new stdClass();

        $result = new SimpleHydrator()->hydrate(['name' => 'flower'], $object);

        self::assertSame($object, $result);
        self::assertSame('flower', $object->name);
    }

    #[Test]
    public function hydratesOrdinaryObjectsAndCastsPropertyTypes(): void
    {
        $object = new class () {
            private int $id;

            public function id(): int
            {
                return $this->id;
            }
        };

        $result = new SimpleHydrator()->hydrate(['id' => '7'], $object);

        self::assertSame($object, $result);
        self::assertSame(7, $object->id());
    }

    #[Test]
    public function skipsVirtualPropertiesDuringHydration(): void
    {
        $object = new class () {
            private string $value = 'initial';

            public string $virtual {
                get => $this->value;
            }

            public function value(): string
            {
                return $this->value;
            }
        };

        new SimpleHydrator()->hydrate(['virtual' => 'updated'], $object);

        self::assertSame('initial', $object->value());
    }

    #[Test]
    public function extractsFieldsFromAccessibleObjects(): void
    {
        $object = new Collection(['name' => 'flower']);
        $hydrator = new SimpleHydrator();

        self::assertSame('flower', $hydrator->extractField($object, 'name'));
        self::assertNull($hydrator->extractField($object, 'missing'));
    }

    #[Test]
    public function extractsFieldsFromObjectProperties(): void
    {
        $object = new class () extends stdClass {
            private string $name = 'flower';
        };
        $hydrator = new SimpleHydrator();

        self::assertSame('flower', $hydrator->extractField($object, 'name'));
        self::assertNull($hydrator->extractField($object, 'missing'));
    }

    #[Test]
    public function extractsFieldsFromTraversables(): void
    {
        $hydrator = new SimpleHydrator();
        $object = new class (['name' => 'flower']) extends ArrayIterator {};

        self::assertSame('flower', $hydrator->extractField($object, 'name'));
        self::assertNull($hydrator->extractField($object, 'missing'));
    }

    #[Test]
    public function returnsNullForFieldsNotPresentOnObjects(): void
    {
        self::assertNull(new SimpleHydrator()->extractField(new class () extends stdClass {}, 'missing'));
    }
}
