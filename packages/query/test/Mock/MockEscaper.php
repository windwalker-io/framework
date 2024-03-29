<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Mock;

/**
 * The MockEscaper class.
 */
class MockEscaper
{
    public function escape(string $value): string
    {
        $text = str_replace("'", "''", $value);

        return addcslashes($text, "\000\n\r\\\032");
    }
}
