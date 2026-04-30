<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use Windwalker\Cache\Storage\ArrayStorage;
use Windwalker\Cache\Storage\PrunableStorageInterface;

/**
 * Tests for ArrayStorage (in-memory cache).
 */
class ArrayStorageTest extends AbstractStorageTestCase
{
    protected function setUp(): void
    {
        $this->instance = new ArrayStorage();
    }

    /**
     * @see ArrayStorage::save — expiration=0 means "never expires"
     */
    public function testSaveWithZeroExpirationNeverExpires(): void
    {
        $this->instance->save('immortal', 'VALUE', 0);

        // Wait a moment to ensure we're past time() at save
        usleep(1000);

        self::assertTrue($this->instance->has('immortal'), 'expiration=0 must mean never expires');
        self::assertEquals('VALUE', $this->instance->get('immortal'));
    }

    /**
     * @see ArrayStorage::has — must handle expiration=0 correctly (never expires)
     */
    public function testHasWithZeroExpirationReturnsTrue(): void
    {
        $this->instance->save('immortal', 'VALUE', 0);

        self::assertTrue($this->instance->has('immortal'));
    }

    /**
     * @see ArrayStorage::has — expired items return false even if stored
     */
    public function testHasReturnsFalseForExpiredItems(): void
    {
        $this->instance->save('expired', 'VALUE', time() - 10);

        self::assertFalse($this->instance->has('expired'));
    }

    /**
     * @see ArrayStorage::get — expired items return null
     */
    public function testGetReturnsNullForExpiredItems(): void
    {
        $this->instance->save('expired', 'VALUE', time() - 10);

        self::assertNull($this->instance->get('expired'));
    }

    /**
     * @see ArrayStorage::get — missing keys return null
     */
    public function testGetReturnsNullForMissingKeys(): void
    {
        self::assertNull($this->instance->get('nonexistent'));
    }

    /**
     * @see ArrayStorage::remove — removing non-existent key is a no-op
     */
    public function testRemoveNonExistentKeyReturnsTrue(): void
    {
        self::assertTrue($this->instance->remove('nonexistent'));
    }

    /**
     * @see ArrayStorage::clear — clears all data including metadata
     */
    public function testClearRemovesAllData(): void
    {
        $this->instance->save('key1', 'val1');
        $this->instance->save('key2', 'val2', 0);
        $this->instance->save('key3', 'val3', time() + 3600);

        $this->instance->clear();

        self::assertEmpty($this->instance->getData(), 'clear() must remove all internal data');
        self::assertFalse($this->instance->has('key1'));
        self::assertFalse($this->instance->has('key2'));
        self::assertFalse($this->instance->has('key3'));
    }

    /**
     * @see ArrayStorage::getData / setData — direct data access for testing/debugging
     */
    public function testGetSetData(): void
    {
        $this->instance->save('foo', 'bar', 0);

        $data = $this->instance->getData();

        self::assertIsArray($data);
        self::assertArrayHasKey('foo', $data);

        $newData = ['baz' => [time() + 3600, 'qux']];
        $this->instance->setData($newData);

        self::assertEquals('qux', $this->instance->get('baz'));
        self::assertFalse($this->instance->has('foo'), 'setData replaces all existing data');
    }

    /**
     * @see ArrayStorage — data persists only within the same instance (per-process)
     */
    public function testDataIsNotSharedBetweenInstances(): void
    {
        $storage1 = new ArrayStorage();
        $storage2 = new ArrayStorage();

        $storage1->save('key', 'value1');
        $storage2->save('key', 'value2');

        self::assertEquals('value1', $storage1->get('key'));
        self::assertEquals('value2', $storage2->get('key'));
    }

    /**
     * @see ArrayStorage::save — overwriting existing key replaces data
     */
    public function testSaveOverwritesExistingKey(): void
    {
        $this->instance->save('key', 'old', time() + 3600);
        $this->instance->save('key', 'new', 0);

        self::assertEquals('new', $this->instance->get('key'));
    }

    /**
     * @see ArrayStorage — stores complex data types (arrays, objects)
     */
    public function testStoresComplexDataTypes(): void
    {
        $array = ['foo' => 'bar', 'nested' => ['key' => 'val']];
        $object = (object) ['prop' => 'value'];

        $this->instance->save('array', $array);
        $this->instance->save('object', $object);

        self::assertEquals($array, $this->instance->get('array'));
        self::assertEquals($object, $this->instance->get('object'));
    }

    public function testImplementsPrunableStorageInterface(): void
    {
        self::assertInstanceOf(PrunableStorageInterface::class, $this->instance);
    }

    public function testPruneRemovesExpiredEntries(): void
    {
        $this->instance->save('expired-1', 'VALUE', time() - 10);
        $this->instance->save('expired-2', 'VALUE', time() - 1);
        $this->instance->save('active', 'VALUE', time() + 60);
        $this->instance->save('forever', 'VALUE', 0);

        self::assertSame(2, $this->instance->prune());
        self::assertFalse($this->instance->has('expired-1'));
        self::assertFalse($this->instance->has('expired-2'));
        self::assertTrue($this->instance->has('active'));
        self::assertTrue($this->instance->has('forever'));
        self::assertSame(0, $this->instance->prune());
    }
}

