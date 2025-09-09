<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Exception;

trait VerbosityExceptionTrait
{
    public function getMessageByVerbosity(int|bool $verbosity = false): string
    {
        if ($verbosity) {
            return $this->getDebugMessage();
        }

        return $this->getMessage();
    }
}
