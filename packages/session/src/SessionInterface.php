<?php

declare(strict_types=1);

namespace Windwalker\Session;

use Countable;
use JsonSerializable;
use Windwalker\Utilities\Contract\AccessorAccessibleInterface;

/**
 * Interface SessionInterface
 */
interface SessionInterface extends Countable, JsonSerializable, AccessorAccessibleInterface
{
    public const OPTION_AUTO_COMMIT = 'auto_commit';
    public const OPTION_AUTO_START = 'auto_start';

    public function clear(): bool;

    public function isStarted(): bool;
}
