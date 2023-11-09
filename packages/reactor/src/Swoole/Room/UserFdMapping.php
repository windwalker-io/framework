<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole\Room;

class UserFdMapping
{
    protected DoubleMapping $mapping;

    public function __construct(protected int $size = 1024, protected int $mapSize = 32768)
    {
        $this->mapping = new DoubleMapping($this->size, $this->mapSize);
    }

    public function addUserFd(string|int $userId, int $fd): void
    {
        $this->mapping->addMap(
            (string) $userId,
            (string) $fd
        );
    }

    public function removeUserFd(string|int $userId, int $fd): void
    {
        $this->mapping->removeMap(
            (string) $userId,
            (string) $fd
        );
    }

    /**
     * @param  string|int  $userId
     *
     * @return  array<int>
     */
    public function getUserFds(string|int $userId): array
    {
        return array_map(
            'intval',
            $this->mapping->getBListOfA((string) $userId)
        );
    }

    /**
     * @param  int  $fd
     *
     * @return  array<string>
     */
    public function removeFd(int $fd): array
    {
        return $this->mapping->removeB((string) $fd);
    }

    /**
     * @param  string|int  $userId
     *
     * @return  array<int>
     */
    public function removeUser(string|int $userId): array
    {
        return array_map(
            'intval',
            $this->mapping->removeA((string) $userId)
        );
    }
}
