<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Stub;

use Psr\Http\Message\MessageInterface;
use Windwalker\Http\MessageTrait;

/**
 * The StubMessage class.
 *
 * @since  2.1
 */
class StubMessage implements MessageInterface
{
    use MessageTrait;
}
