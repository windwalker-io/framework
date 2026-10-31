<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Failer;

use PHPUnit\Framework\Attributes\Before;
use Windwalker\Queue\Failer\PdoQueueFailer;

/**
 * The DatabaseQueueFailerTest class.
 */
class PdoQueueFailerTest extends DatabaseQueueFailerTest
{
    #[Before]
    protected function createFailerInstance(): void
    {
        $conn = self::$db->getDriver()->getConnection();

        $this->instance = new PdoQueueFailer(
            $conn->get()
        );

        $conn->release();
    }
}
