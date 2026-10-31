<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Hydrator;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Hydrator\AccessibleHydrator;
use Windwalker\Data\Collection;
use Windwalker\Utilities\Contract\AccessorAccessibleInterface;
use Windwalker\Utilities\Contract\DumpableInterface;

class AccessibleHydratorTest extends TestCase
{
    #[Test]
    public function extractsDumpableObjects(): void
    {
        $object = new AccessibleHydratorFixture(['name' => 'flower']);

        self::assertSame(['name' => 'flower'], new AccessibleHydrator()->extract($object));
    }

    #[Test]
    public function rejectsObjectsThatCannotBeDumped(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs(
            AccessibleHydrator::class . '::extract() expects a ' . DumpableInterface::class . ' object'
        );

        new AccessibleHydrator()->extract(new \stdClass());
    }

    #[Test]
    public function hydratesArrayObjectsAndReplacesNullValues(): void
    {
        $object = new Collection(['existing' => null]);

        $result = new AccessibleHydrator()->hydrate(['existing' => 'value', 'new' => 'added'], $object);

        self::assertSame($object, $result);
        self::assertSame(['existing' => 'value', 'new' => 'added'], $object->dump());
    }

    #[Test]
    public function hydratesAccessorAccessibleObjects(): void
    {
        $object = new AccessibleHydratorFixture();

        $result = new AccessibleHydrator()->hydrate(['name' => 'flower'], $object);

        self::assertSame($object, $result);
        self::assertSame(['name' => 'flower'], $object->dump());
    }

    #[Test]
    public function rejectsObjectsThatCannotBeHydrated(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs(
            AccessibleHydrator::class
            . '::hydrate() expects an '
            . \Windwalker\Scalars\ArrayObject::class
            . ' or '
            . AccessorAccessibleInterface::class
        );

        new AccessibleHydrator()->hydrate([], new \stdClass());
    }
}

class AccessibleHydratorFixture implements AccessorAccessibleInterface, DumpableInterface
{
    private array $values = [];

    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function &get(mixed $key): mixed
    {
        if (!array_key_exists($key, $this->values)) {
            $this->values[$key] = null;
        }

        return $this->values[$key];
    }

    public function set(mixed $key, mixed $value): static
    {
        $this->values[$key] = $value;

        return $this;
    }

    public function def(mixed $key, mixed $default): mixed
    {
        return $this->values[$key] ??= $default;
    }

    public function has(mixed $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function remove(mixed $key): static
    {
        unset($this->values[$key]);

        return $this;
    }

    public function dump(bool $recursive = false, bool $onlyDumpable = false): array
    {
        return $this->values;
    }
}
