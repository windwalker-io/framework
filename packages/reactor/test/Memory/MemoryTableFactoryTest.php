<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Test\Memory;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Reactor\Memory\ArrayTable;
use Windwalker\Reactor\Memory\MemoryTableFactory;
use Windwalker\Reactor\Memory\SwooleTable;

use function Windwalker\swoole_installed;

class MemoryTableFactoryTest extends TestCase
{
    protected MemoryTableFactory $instance;

    protected function setUp(): void
    {
        $this->instance = new MemoryTableFactory();
    }

    #[Test]
    public function createMemoryTableChoosesImplementationByExtension(): void
    {
        $table = $this->instance->createMemoryTable(16);

        self::assertInstanceOf(
            swoole_installed() ? SwooleTable::class : ArrayTable::class,
            $table
        );
    }

    #[Test]
    public function createdRoomMappingSharesUserFdMappingAndWorks(): void
    {
        $userFd = $this->instance->createUserFdMapping(16, 1024);
        $rooms = $this->instance->createRoomMapping($userFd, 16, 1024);

        $rooms->joinRoom('lobby', 5);

        self::assertSame([5], $rooms->getRoomFds('lobby'));
        self::assertSame(['lobby'], $rooms->getFdRooms(5));
    }

    #[Test]
    public function createdUserFdMappingWorks(): void
    {
        $mapping = $this->instance->createUserFdMapping(16, 1024);

        $mapping->addUserFd(1, 10);

        self::assertSame([10], $mapping->getUserFds(1));
    }
}
