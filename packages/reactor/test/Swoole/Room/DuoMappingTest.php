<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Test\Swoole\Room;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Reactor\Memory\ArrayTable;
use Windwalker\Reactor\Swoole\Room\DuoMapping;

class DuoMappingTest extends TestCase
{
    protected DuoMapping $instance;

    #[Before]
    public function createMapping(): void
    {
        $this->instance = new DuoMapping(new ArrayTable(), new ArrayTable(), 1024);
    }

    #[Test]
    public function addMapRegistersBothDirections(): void
    {
        $this->instance->addMap('a1', 'b1');
        $this->instance->addMap('a1', 'b2');
        $this->instance->addMap('a2', 'b1');

        self::assertSame(['b1', 'b2'], array_values($this->instance->getBListOfA('a1')));
        self::assertSame(['a1', 'a2'], array_values($this->instance->getAListOfB('b1')));
        self::assertSame(['a1'], array_values($this->instance->getAListOfB('b2')));
    }

    #[Test]
    public function addMapDoesNotDuplicateExistingRelation(): void
    {
        $this->instance->addMap('a1', 'b1');
        $this->instance->addMap('a1', 'b1');

        self::assertSame(['b1'], $this->instance->getBListOfA('a1'));
        self::assertSame(['a1'], $this->instance->getAListOfB('b1'));
    }

    #[Test]
    public function addAToBReturnsUpdatedList(): void
    {
        $this->instance->addAToB('a1', 'b1');

        self::assertSame(['b1', 'b2'], $this->instance->addAToB('a1', 'b2'));
    }

    #[Test]
    public function addBToAReturnsUpdatedList(): void
    {
        $this->instance->addBToA('b1', 'a1');

        self::assertSame(['a1', 'a2'], $this->instance->addBToA('b1', 'a2'));
    }

    #[Test]
    public function getListsReturnEmptyArrayForUnknownKey(): void
    {
        self::assertSame([], $this->instance->getBListOfA('none'));
        self::assertSame([], $this->instance->getAListOfB('none'));
    }

    #[Test]
    public function removeMapRemovesBothDirectionsOnly(): void
    {
        $this->instance->addMap('a1', 'b1');
        $this->instance->addMap('a1', 'b2');

        $this->instance->removeMap('a1', 'b1');

        self::assertSame(['b2'], array_values($this->instance->getBListOfA('a1')));
        self::assertSame([], array_values($this->instance->getAListOfB('b1')));
        self::assertSame(['a1'], array_values($this->instance->getAListOfB('b2')));
    }

    #[Test]
    public function removeMapOfUnknownKeysDoesNothing(): void
    {
        $this->instance->removeMap('none', 'none');

        self::assertSame([], $this->instance->getBListOfA('none'));
        self::assertSame([], $this->instance->getAListOfB('none'));
    }

    #[Test]
    public function removeAReturnsRelatedBsAndCleansReverseMap(): void
    {
        $this->instance->addMap('a1', 'b1');
        $this->instance->addMap('a1', 'b2');
        $this->instance->addMap('a2', 'b1');

        $removed = $this->instance->removeA('a1');

        self::assertSame(['b1', 'b2'], array_values($removed));
        self::assertSame([], $this->instance->getBListOfA('a1'));
        self::assertSame(['a2'], array_values($this->instance->getAListOfB('b1')));
        self::assertSame([], array_values($this->instance->getAListOfB('b2')));
    }

    #[Test]
    public function removeBReturnsRelatedAsAndCleansReverseMap(): void
    {
        $this->instance->addMap('a1', 'b1');
        $this->instance->addMap('a2', 'b1');
        $this->instance->addMap('a2', 'b2');

        $removed = $this->instance->removeB('b1');

        self::assertSame(['a1', 'a2'], array_values($removed));
        self::assertSame([], $this->instance->getAListOfB('b1'));
        self::assertSame(['b2'], array_values($this->instance->getBListOfA('a2')));
        self::assertSame([], array_values($this->instance->getBListOfA('a1')));
    }
}
