<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Event;

use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Event\JobEventTrait;

class JobEventTraitTest extends TestCase
{
    public function testJobNonCallable(): void
    {
        $this->expectException(\TypeError::class);

        $event = new class () {
            use JobEventTrait;
        };

        $event->job = 123;
    }
}
