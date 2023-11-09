<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Memory;

use Windwalker\Reactor\Swoole\Room\DuoMapping;

use Windwalker\Reactor\Swoole\Room\RoomMapping;
use Windwalker\Reactor\Swoole\Room\UserFdMapping;

use function Windwalker\swoole_installed;

class MemoryTableFactory
{
    public function createMemoryTable(int $size): MemoryTableInterface
    {
        return swoole_installed()
            ? new SwooleTable($size)
            : new ArrayTable();
    }

    public function createDuoMapping(int $size = 1024, int $mapSize = 32768): DuoMapping
    {
        return new DuoMapping(
            $this->createMemoryTable($size),
            $this->createMemoryTable($size),
            $mapSize
        );
    }

    public function createUserFdMapping(int $size = 1024, int $mapSize = 32768): UserFdMapping
    {
        return new UserFdMapping($this->createDuoMapping($size, $mapSize));
    }

    public function createRoomMapping(UserFdMapping $userFdMapping, int $size = 1024, int $mapSize = 32768): RoomMapping
    {
        return new RoomMapping(
            $this->createDuoMapping($size, $mapSize),
            $userFdMapping
        );
    }
}
