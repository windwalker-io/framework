<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Windwalker\Event\EventListenableInterface;
use Windwalker\Event\EventListenableTrait;
use Windwalker\Utilities\Classes\OptionAccessTrait;

/**
 * The Server class.
 */
abstract class AbstractServer implements ServerInterface
{
    use EventListenableTrait;
}
