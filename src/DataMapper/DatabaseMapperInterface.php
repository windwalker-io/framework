<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\DataMapper;

use Windwalker\Database\Driver\AbstractDatabaseDriver;

/**
 * The DatabaseMapperInterface class.
 *
 * @since  3.0
 */
interface DatabaseMapperInterface extends DataMapperInterface
{
    /**
     * Get table fields.
     *
     * @param string $table Table name.
     *
     * @return  array
     */
    public function getFields($table = null);

    /**
     * Get table name.
     *
     * @return  string Table name.
     */
    public function getTable();

    /**
     * Get DB adapter.
     *
     * @return  AbstractDatabaseDriver Db adapter.
     */
    public function getDb();

    /**
     * Set db adapter.
     *
     * @param   AbstractDatabaseDriver $db Db adapter.
     *
     * @return  DataMapper  Return self to support chaining.
     */
    public function setDb(AbstractDatabaseDriver $db);
}
