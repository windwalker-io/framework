<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use PHPUnit\Framework\TestCase;
use Windwalker\Cache\Storage\StorageInterface;

/**
 * The AbstractStorageTest class.
 */
abstract class AbstractStorageTestCase extends TestCase
{
    /**
     * @var StorageInterface
     */
    protected $instance;

    /**
     * @see  StorageInterface::save
     */
    public function testSave(): void
    {
        $r = $this->instance->save('hello', 'FOOOOOOOOOO');

        $value = $this->instance->get('hello');

        self::assertEquals('FOOOOOOOOOO', $value);
        self::assertTrue($r);
    }

    /**
     * @see  StorageInterface::save
     */
    public function testSaveWithExpiration(): void
    {
        $this->instance->save('hello', 'FOOOOOOOOOO', time() - 1);

        $value = $this->instance->get('hello');

        self::assertNull($value);
    }

    /**
     * @see  StorageInterface::get
     */
    public function testGet(): void
    {
        self::assertNull($this->instance->get('hello'));

        $this->instance->save('hello', 'FOOOOOOOOOO');

        self::assertEquals('FOOOOOOOOOO', $this->instance->get('hello'));
    }

    /**
     * @see  StorageInterface::has
     */
    public function testHas(): void
    {
        self::assertFalse($this->instance->has('hello'));

        $this->instance->save('hello', 'FOOOOOOOOOO');

        self::assertTrue($this->instance->has('hello'));
    }

    /**
     * @see  StorageInterface::has
     */
    public function testHasExpired(): void
    {
        $this->instance->save('hello', 'FOOOOOOOOOO', time() - 1);

        self::assertFalse($this->instance->has('hello'));
    }

    /**
     * @see  StorageInterface::remove
     */
    public function testRemove(): void
    {
        $this->instance->save('hello', 'FOOOOOOOOOO');

        self::assertTrue($this->instance->has('hello'));

        $this->instance->remove('hello');

        self::assertFalse($this->instance->has('hello'));
    }

    /**
     * @see  StorageInterface::clear
     */
    public function testClear(): void
    {
        foreach (range(1, 5) as $i) {
            $this->instance->save('foo' . $i, 'FOO' . $i);
        }

        $this->instance->clear();

        self::assertFalse($this->instance->has('foo1'));
        self::assertFalse($this->instance->has('foo3'));
        self::assertFalse($this->instance->has('foo5'));
    }

    protected function tearDown(): void
    {
        $this->instance->clear();
    }
}
