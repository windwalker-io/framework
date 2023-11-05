<?php

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Windwalker\Event\EventAwareTrait;

/**
 * The Server class.
 *
 * @deprecated  Deprecated with no instead.
 */
abstract class AbstractServer implements ServerInterface
{
    use EventAwareTrait;
}
