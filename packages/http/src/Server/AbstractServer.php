<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Windwalker\Event\EventAwareTrait;

/**
 * The Server class.
 */
abstract class AbstractServer implements ServerInterface
{
    use EventAwareTrait;
}
