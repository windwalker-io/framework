<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2017 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Query\Test;

/**
 * QueryTestTrait
 *
 * @since  3.2.7
 */
trait QueryTestTrait
{
    /**
     * testSuffix
     *
     * @return  void
     */
    public function testSuffix()
    {
        $query = $this->getQuery()
            ->select('*')
            ->from('foo')
            ->where('a = b')
            ->order('id')
            ->suffix('FOR UPDATE');

        $sql = 'SELECT * FROM foo WHERE a = b ORDER BY id FOR UPDATE';

        $this->assertEquals($this->format($sql), $this->format($query));
    }

    /**
     * testForUpdate
     *
     * @return  void
     */
    public function testForUpdate()
    {
        $query = $this->getQuery()
            ->select('*')
            ->from('foo')
            ->where('a = b')
            ->order('id')
            ->forUpdate();

        $sql = 'SELECT * FROM foo WHERE a = b ORDER BY id FOR UPDATE';

        $this->assertEquals($this->format($sql), $this->format($query));
    }
}
