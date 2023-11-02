<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Exception;

/**
 * The DatabaseQueryException class.
 */
class DatabaseQueryException extends DatabaseException
{
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
}
