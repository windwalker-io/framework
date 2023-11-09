<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Room;

class RoomMapping
{
    protected DoubleMapping $mapping;

    public function __construct(
        protected UserFdMapping $userMapping,
        protected int $size = 1024,
        protected int $mapSize = 32768
    ) {
        $this->mapping = new DoubleMapping($this->size, $this->mapSize);
    }

    /**
     * @param  string  $room
     *
     * @return  array<int>
     */
    public function getRoomFds(string $room): array
    {
        return array_map(
            'intval',
            $this->mapping->getBListOfA($room)
        );
    }

    /**
     * @param  int  $fd
     *
     * @return  array<string>
     */
    public function getFdRooms(int $fd): array
    {
        return $this->mapping->getAListOfB((string) $fd);
    }

    public function joinRoom(string $room, int $fd): void
    {
        $this->mapping->addMap($room, (string) $fd);
    }

    public function leaveRoom(string $room, int $fd): void
    {
        $this->mapping->removeMap($room, (string) $fd);
    }

    /**
     * @param  string  $room
     *
     * @return  int[]
     */
    public function clearRoom(string $room): array
    {
        return $this->mapping->removeA($room);
    }

    /**
     * @param  int  $fd
     *
     * @return  string[]
     */
    public function leaveAllRooms(int $fd): array
    {
        return array_map(
            'intval',
            $this->mapping->removeB((string) $fd)
        );
    }
}
