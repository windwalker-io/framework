<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Event;

use Windwalker\Event\AbstractEvent;
use Windwalker\Event\Events\ErrorEventTrait;

/**
 * The ErrorEvent class.
 */
class ErrorEvent extends RequestEvent
{
    use ErrorEventTrait;
}
