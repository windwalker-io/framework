<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Test\Memory;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Reactor\Memory\ArrayTable;
use Windwalker\Reactor\Memory\MemoryTableInterface;

class ArrayTableTest extends TestCase
{
    protected ArrayTable $instance;

    #[Before]
    public function createTable(): void
    {
        $this->instance = new ArrayTable();
        $this->instance->column('name', MemoryTableInterface::TYPE_STRING, 32);
        $this->instance->column('age', MemoryTableInterface::TYPE_INT);
        $this->instance->create();
    }

    #[Test]
    public function setStoresOnlyDeclaredColumnsAndFillsMissingWithNull(): void
    {
        $this->instance->set('a', ['name' => 'Sakura', 'extra' => 'ignored']);

        self::assertSame(['name' => 'Sakura', 'age' => null], $this->instance->get('a'));
    }

    #[Test]
    public function getWithoutFieldReturnsWholeRow(): void
    {
        $this->instance->set('a', ['name' => 'Sakura', 'age' => 18]);

        self::assertSame(['name' => 'Sakura', 'age' => 18], $this->instance->get('a'));
    }

    #[Test]
    public function getWithFieldReturnsColumnValue(): void
    {
        $this->instance->set('a', ['name' => 'Sakura', 'age' => 18]);

        self::assertSame('Sakura', $this->instance->get('a', 'name'));
        self::assertSame(18, $this->instance->get('a', 'age'));
        self::assertNull($this->instance->get('a', 'unknown'));
    }

    #[Test]
    public function getReturnsFalseWhenKeyNotExists(): void
    {
        self::assertFalse($this->instance->get('none'));
        self::assertFalse($this->instance->get('none', 'name'));
    }

    #[Test]
    public function existsReflectsSetAndDelete(): void
    {
        self::assertFalse($this->instance->exists('a'));

        $this->instance->set('a', ['name' => 'Sakura']);

        self::assertTrue($this->instance->exists('a'));

        $this->instance->delete('a');

        self::assertFalse($this->instance->exists('a'));
    }

    #[Test]
    public function countReturnsNumberOfRows(): void
    {
        $this->instance->set('a', ['name' => 'A']);
        $this->instance->set('b', ['name' => 'B']);
        $this->instance->set('a', ['name' => 'A2']);

        self::assertCount(2, $this->instance);
    }

    #[Test]
    public function destroyClearsAllRows(): void
    {
        $this->instance->set('a', ['name' => 'A']);

        self::assertTrue($this->instance->destroy());
        self::assertCount(0, $this->instance);
    }
}
