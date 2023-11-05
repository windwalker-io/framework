<?php

declare(strict_types=1);

namespace Windwalker\Test\Traits;

use SqlFormatter;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Query;
use Windwalker\Test\Helper\TestStringHelper;
use Windwalker\Utilities\Str;

/**
 * The AbstractQueryTestCase class.
 *
 * @since  2.1
 */
trait QueryTestTrait
{
    use BaseAssertionTrait;

    /**
     * Property quote.
     *
     * @var  array
     */
    protected static array $nameQuote = ['"', '"'];

    protected static function qn(string $text): string
    {
        return Str::surrounds($text, static::$nameQuote);
    }

    protected static function replaceQn(string $sql): string
    {
        $sql = str_replace('\\"', '__QQ__', $sql);

        if (static::$nameQuote !== ['"', '"']) {
            $sql = preg_replace('/(\"([\w]+)\")/', Str::surrounds('$2', static::$nameQuote), $sql);
        }

        return str_replace('__QQ__', '"', $sql);
    }

    protected static function renderQuery($query): string
    {
        if ($query instanceof Query) {
            $query = BoundedHelper::emulatePrepared(
                $query,
                $query->render(false, $bounded),
                $bounded
            );
        }

        return $query;
    }

    /**
     * format
     *
     * @param  string  $sql
     *
     * @return  String
     */
    protected static function format(string $sql): string
    {
        return SqlFormatter::format((string) $sql, false);
    }

    protected static function compress(string $sql): string
    {
        return SqlFormatter::compress((string) $sql);
    }

    public static function assertSqlFormatEquals($sql1, $sql2): void
    {
        self::assertEquals(
            self::format(static::replaceQn($sql1)),
            self::format(static::renderQuery($sql2))
        );
    }

    public static function assertSqlEquals($sql1, $sql2): void
    {
        self::assertEquals(
            static::compress(static::replaceQn($sql1)),
            static::compress(static::renderQuery($sql2))
        );
    }
}
