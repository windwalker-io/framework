<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Database\Test;

use Windwalker\Query\Query;
use Windwalker\Test\Helper\TestStringHelper;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * The AbstractQueryTestCase class.
 *
 * @since  2.1
 */
abstract class AbstractQueryTestCase extends \PHPUnit\Framework\TestCase
{
    use BaseAssertionTrait;

    /**
     * Property quote.
     *
     * @var  array
     */
    protected static $quote = ['"', '"'];

    /**
     * quote
     *
     * @param string $text
     *
     * @return  string
     */
    protected function qn($text)
    {
        return TestStringHelper::quote($text, static::$quote);
    }

    /**
     * format
     *
     * @param   string $sql
     *
     * @return  String
     */
    protected function format($sql)
    {
        return \SqlFormatter::format((string) $sql, false);
    }
}
