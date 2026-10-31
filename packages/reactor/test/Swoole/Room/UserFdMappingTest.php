<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Test\Swoole\Room;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Reactor\Memory\ArrayTable;
use Windwalker\Reactor\Swoole\Room\DuoMapping;
use Windwalker\Reactor\Swoole\Room\UserFdMapping;

class UserFdMappingTest extends TestCase
{
    protected UserFdMapping $instance;

    #[Before]
    public function createMapping(): void
    {
        $this->instance = new UserFdMapping(new DuoMapping(new ArrayTable(), new ArrayTable(), 1024));
    }

    #[Test]
    public function addUserFdAcceptsIntAndStringUserIdAndReturnsIntFds(): void
    {
        $this->instance->addUserFd(1, 10);
        $this->instance->addUserFd('1', 11);

        self::assertSame([10, 11], array_values($this->instance->getUserFds(1)));
        self::assertSame([10, 11], array_values($this->instance->getUserFds('1')));
    }

    #[Test]
    public function removeUserFdRemovesOnlyThatFd(): void
    {
        $this->instance->addUserFd(1, 10);
        $this->instance->addUserFd(1, 11);

        $this->instance->removeUserFd(1, 10);

        self::assertSame([11], array_values($this->instance->getUserFds(1)));
    }

    #[Test]
    public function removeFdReturnsOwnersAndDetachesFdFromThem(): void
    {
        $this->instance->addUserFd(1, 10);
        $this->instance->addUserFd(1, 11);

        self::assertSame(['1'], array_values($this->instance->removeFd(10)));
        self::assertSame([11], array_values($this->instance->getUserFds(1)));
    }

    #[Test]
    public function removeUserReturnsIntFdsAndClearsUser(): void
    {
        $this->instance->addUserFd(1, 10);
        $this->instance->addUserFd(1, 11);

        self::assertSame([10, 11], array_values($this->instance->removeUser(1)));
        self::assertSame([], $this->instance->getUserFds(1));
    }
}
