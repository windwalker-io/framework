<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Exception;

interface VerbosityExceptionInterface
{
    public function getDebugMessage(): string;

    public function getMessageByVerbosity(int|bool $verbosity = false): string;
}
