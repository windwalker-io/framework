<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Test\Failer;

use Windwalker\Queue\Failer\PdoQueueFailer;

/**
 * The DatabaseQueueFailerTest class.
 */
class PdoQueueFailerTest extends DatabaseQueueFailerTest
{
    protected function setUp(): void
    {
        $conn = self::$db->getDriver()->getConnection();

        $this->instance = new PdoQueueFailer(
            $conn->get()
        );

        $conn->release();
    }

    protected function tearDown(): void
    {
    }
}
