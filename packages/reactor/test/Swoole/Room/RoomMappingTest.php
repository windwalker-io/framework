<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Test\Swoole\Room;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Reactor\Memory\ArrayTable;
use Windwalker\Reactor\Swoole\Room\DuoMapping;
use Windwalker\Reactor\Swoole\Room\RoomMapping;
use Windwalker\Reactor\Swoole\Room\UserFdMapping;

class RoomMappingTest extends TestCase
{
    protected RoomMapping $instance;

    #[Before]
    public function createMapping(): void
    {
        $this->instance = new RoomMapping(
            new DuoMapping(new ArrayTable(), new ArrayTable(), 1024),
            new UserFdMapping(new DuoMapping(new ArrayTable(), new ArrayTable(), 1024))
        );
    }

    #[Test]
    public function joinRoomMakesFdVisibleFromBothSides(): void
    {
        $this->instance->joinRoom('lobby', 1);
        $this->instance->joinRoom('lobby', 2);
        $this->instance->joinRoom('game', 1);

        self::assertSame([1, 2], array_values($this->instance->getRoomFds('lobby')));
        self::assertSame(['lobby', 'game'], array_values($this->instance->getFdRooms(1)));
    }

    #[Test]
    public function leaveRoomRemovesOnlyThatRelation(): void
    {
        $this->instance->joinRoom('lobby', 1);
        $this->instance->joinRoom('lobby', 2);

        $this->instance->leaveRoom('lobby', 1);

        self::assertSame([2], array_values($this->instance->getRoomFds('lobby')));
        self::assertSame([], array_values($this->instance->getFdRooms(1)));
    }

    #[Test]
    public function clearRoomReturnsFdsAndRemovesRoomFromThem(): void
    {
        $this->instance->joinRoom('lobby', 1);
        $this->instance->joinRoom('game', 1);

        self::assertSame(['1'], array_values($this->instance->clearRoom('lobby')));
        self::assertSame([], $this->instance->getRoomFds('lobby'));
        self::assertSame(['game'], array_values($this->instance->getFdRooms(1)));
    }

    #[Test]
    public function leaveAllRoomsReturnsRoomsAndEmptiesFd(): void
    {
        $this->instance->joinRoom('lobby', 1);
        $this->instance->joinRoom('game', 1);
        $this->instance->joinRoom('game', 2);

        self::assertSame(['lobby', 'game'], array_values($this->instance->leaveAllRooms(1)));
        self::assertSame([], $this->instance->getFdRooms(1));
        self::assertSame([2], array_values($this->instance->getRoomFds('game')));
        self::assertSame([], array_values($this->instance->getRoomFds('lobby')));
    }
}
