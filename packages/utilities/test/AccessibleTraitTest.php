<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use ArrayObject;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Accessible\AccessibleTrait;
use Windwalker\Utilities\Test\Stub\StubAccessible;

class AccessibleTraitTest extends TestCase
{
    /**
     * @var StubAccessible
     */
    protected $instance;

    /**
     * @see  AccessibleTrait::set
     */
    #[Test]
    public function setStoresValueByKey(): void
    {
        $this->instance->set('foo', 123);

        self::assertEquals(123, $this->instance['foo']);
    }

    /**
     * @see  AccessibleTrait::jsonSerialize
     */
    #[Test]
    public function jsonSerializeEncodesStorageAsJsonArray(): void
    {
        self::assertEquals('[1,2,3]', json_encode($this->instance));
    }

    /**
     * @see  AccessibleTrait::remove
     */
    #[Test]
    public function removeDeletesItemByKey(): void
    {
        $this->instance->remove(2);

        self::assertEquals([1, 2], $this->instance->dump());
    }

    /**
     * @see  AccessibleTrait::reset
     */
    #[Test]
    public function resetReplacesStorage(): void
    {
        $this->instance->reset([1, 1, 1]);

        self::assertEquals([1, 1, 1], $this->instance->dump());
    }

    /**
     * @see  AccessibleTrait::__unset
     */
    #[Test]
    public function magicUnsetRemovesItem(): void
    {
        unset($this->instance->{1});

        self::assertNull($this->instance[1]);
    }

    /**
     * @see  AccessibleTrait::__set
     */
    #[Test]
    public function magicSetAddsItemToStorage(): void
    {
        $this->instance->foo = 'qwe';

        self::assertEquals([1, 2, 3, 'foo' => 'qwe'], $this->instance->dump());
    }

    /**
     * @see  AccessibleTrait::isNull
     */
    #[Test]
    public function isNullReflectsWhetherStorageIsEmpty(): void
    {
        self::assertFalse($this->instance->isNull());

        $this->instance->reset();

        self::assertTrue($this->instance->isNull());
    }

    /**
     * @see  AccessibleTrait::count
     */
    #[Test]
    public function countReturnsNumberOfItemsAndTracksChanges(): void
    {
        self::assertEquals(3, $this->instance->count());

        $this->instance[] = 4;

        self::assertCount(4, $this->instance);
    }

    /**
     * @see  AccessibleTrait::offsetUnset
     */
    #[Test]
    public function offsetUnsetRemovesItem(): void
    {
        unset($this->instance[2]);

        self::assertEquals([1, 2], $this->instance->dump());
    }

    /**
     * @see  AccessibleTrait::def
     */
    #[Test]
    public function defSetsValueOnlyWhenKeyIsMissing(): void
    {
        $this->instance->def(1, 'qwe');
        $this->instance->def(3, 'flower');

        self::assertEquals([1, 2, 3, 'flower'], $this->instance->dump());
    }

    #[Test]
    public function dumpReturnsStorageAsArray(): void
    {
        self::assertEquals([1, 2, 3], $this->instance->dump());
    }

    #[Test]
    public function dumpRecursivelyConvertsNestedObjectsToArrays(): void
    {
        $this->resetWithNestedObjects();

        self::assertEquals(
            [
                1,
                2,
                ['foo' => 'bar'],
                ['flower' => 'sakura'],
                ['car' => 'jeep'],
            ],
            $this->instance->dump(true)
        );
    }

    #[Test]
    public function dumpRecursivelyOnlyConvertsDumpableObjectsWhenOnlyDumpableIsSet(): void
    {
        $this->resetWithNestedObjects();

        self::assertEquals(
            [
                1,
                2,
                (object) ['foo' => 'bar'],
                new ArrayObject(['flower' => 'sakura']),
                ['car' => 'jeep'],
            ],
            $this->instance->dump(true, true)
        );
    }

    protected function resetWithNestedObjects(): void
    {
        $this->instance->reset(
            [
                1,
                2,
                (object) ['foo' => 'bar'],
                new ArrayObject(['flower' => 'sakura']),
                new StubAccessible(['car' => 'jeep']),
            ]
        );
    }

    /**
     * @see  AccessibleTrait::notNull
     */
    #[Test]
    public function notNullReflectsWhetherStorageHasItems(): void
    {
        self::assertTrue($this->instance->notNull());

        $this->instance->reset();

        self::assertFalse($this->instance->notNull());
    }

    /**
     * @see  AccessibleTrait::get
     */
    #[Test]
    public function getReturnsReferenceToStoredValue(): void
    {
        $v = &$this->instance->get(1);

        $this->instance->set(1, 'qqq');

        self::assertEquals('qqq', $v);
    }

    /**
     * @see  AccessibleTrait::__get
     */
    #[Test]
    public function magicGetReturnsReferenceToStoredValue(): void
    {
        $v = &$this->instance->{1};

        $this->instance->set(1, 'qqq');

        self::assertEquals('qqq', $v);
    }

    /**
     * @see  AccessibleTrait::has
     */
    #[Test]
    public function hasDetectsExistingKeys(): void
    {
        self::assertTrue($this->instance->has(1));
        self::assertFalse($this->instance->has(5));
    }

    /**
     * @see  AccessibleTrait::getIterator
     */
    #[Test]
    public function getIteratorAllowsModifyingItemsByReference(): void
    {
        foreach ($this->instance as &$v) {
            $v++;
        }

        self::assertEquals([2, 3, 4], iterator_to_array($this->instance));
    }

    /**
     * @see  AccessibleTrait::offsetSet
     */
    #[Test]
    public function offsetSetStoresValueAtKey(): void
    {
        $this->instance[3] = 'flower';

        self::assertEquals([1, 2, 3, 'flower'], $this->instance->dump());
    }

    /**
     * @see  AccessibleTrait::offsetExists
     */
    #[Test]
    public function offsetExistsDetectsExistingKeys(): void
    {
        self::assertTrue(isset($this->instance[1]));
        self::assertFalse(isset($this->instance[6]));
    }

    /**
     * @see  AccessibleTrait::offsetGet
     */
    #[Test]
    public function offsetGetReturnsReferenceToStoredValue(): void
    {
        $v = &$this->instance[1];

        $this->instance[1] = 'abc';

        self::assertEquals('abc', $v);
    }

    /**
     * @see  AccessibleTrait::__isset
     */
    #[Test]
    public function magicIssetDetectsExistingKeys(): void
    {
        self::assertTrue(isset($this->instance->{1}));
        self::assertFalse(isset($this->instance->{6}));
    }

    #[Before]
    protected function createInstance(): void
    {
        $this->instance = new StubAccessible([1, 2, 3]);
    }
}
