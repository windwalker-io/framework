<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Event\JobEventTrait;

class JobEventTraitTest extends TestCase
{
    #[Test]
    public function assigningJobThrowsError(): void
    {
        $this->expectException(\Error::class);

        $event = new class () {
            use JobEventTrait;
        };

        $event->job = 123;
    }
}
