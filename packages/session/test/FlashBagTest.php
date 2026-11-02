<?php

declare(strict_types=1);

namespace Windwalker\Session\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Session\FlashBag;

class FlashBagTest extends TestCase
{
    #[Test]
    public function linkBindsFlashBagToExternalStorage(): void
    {
        $storage = [];
        $flash = new FlashBag();

        self::assertSame($flash, $flash->link($storage));

        $flash->add('Hello');

        self::assertSame(['info' => ['Hello']], $storage['_flash']);
    }

    #[Test]
    public function linkKeepsExistingMessages(): void
    {
        $storage = ['custom' => ['error' => ['Bad']]];
        $flash = new FlashBag()->link($storage, 'custom');

        self::assertSame(['Bad'], $flash->peek('error'));
    }

    #[Test]
    public function addAppendsMessagesByType(): void
    {
        $flash = new FlashBag();
        $flash->add('One');
        $flash->add('Two');
        $flash->add('Bad', 'error');

        self::assertSame(['info' => ['One', 'Two'], 'error' => ['Bad']], $flash->peek());
    }

    #[Test]
    public function peekReturnsMessagesWithoutRemovingThem(): void
    {
        $flash = new FlashBag(['info' => ['One']]);

        self::assertSame(['One'], $flash->peek('info'));
        self::assertNull($flash->peek('missing'));
        self::assertSame(['One'], $flash->peek('info'));
    }

    #[Test]
    public function getReturnsAndRemovesMessagesOfType(): void
    {
        $flash = new FlashBag(['info' => ['One'], 'error' => ['Bad']]);

        self::assertSame(['One'], $flash->get('info'));
        self::assertNull($flash->get('info'));
        self::assertSame(['error' => ['Bad']], $flash->peek());
    }

    #[Test]
    public function allReturnsAndClearsAllMessages(): void
    {
        $flash = new FlashBag(['info' => ['One']]);

        self::assertSame(['info' => ['One']], $flash->all());
        self::assertSame([], $flash->peek());
    }

    #[Test]
    public function setStorageReplacesMessages(): void
    {
        $flash = new FlashBag();
        $data = ['warning' => ['Careful']];

        self::assertSame($flash, $flash->setStorage($data));
        self::assertSame(['warning' => ['Careful']], $flash->peek());
    }
}
