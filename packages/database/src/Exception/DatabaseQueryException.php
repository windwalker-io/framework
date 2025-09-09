<?php

declare(strict_types=1);

namespace Windwalker\Database\Exception;

use Windwalker\Utilities\Exception\VerbosityExceptionInterface;
use Windwalker\Utilities\Exception\VerbosityExceptionTrait;

/**
 * The DatabaseQueryException class.
 */
class DatabaseQueryException extends DatabaseException implements VerbosityExceptionInterface
{
    use VerbosityExceptionTrait;

    public string $debugSql = '';

    public function getDebugSql(): string
    {
        return $this->debugSql;
    }

    /**
     * @param  string  $debugSql
     *
     * @return  static  Return self to support chaining.
     */
    public function setDebugSql(string $debugSql): static
    {
        $this->debugSql = $debugSql;

        return $this;
    }

    public function getDebugMessage(): string
    {
        return $this->getMessage() . ($this->debugSql ? ' - SQL: ' . $this->debugSql : '');
    }
}
