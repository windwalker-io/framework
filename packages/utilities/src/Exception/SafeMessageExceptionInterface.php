<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Exception;

/**
 * Marks an exception that provides a message safe to show to end users,
 * even when the application verbosity is HIDDEN (e.g. validation errors).
 */
interface SafeMessageExceptionInterface
{
    public function getSafeMessage(): string;
}
