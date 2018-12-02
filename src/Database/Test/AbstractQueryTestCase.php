<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
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
     * getQuery
     *
     * @return  Query
     */
    protected function getQuery()
    {
        return new Query();
    }

    /**
     * testAlias
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function testQueryAlias()
    {
        $query = $this->getQuery()
            ->select('*')
            ->from('foo')
            ->where('a = b')
            ->order('id')
            ->alias('foo');

        $sql = '(SELECT * FROM foo WHERE a = b ORDER BY id) AS foo';

        self::assertEquals($this->format($sql), $this->format($query));
    }

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
