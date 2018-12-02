<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver;

/**
 * DatabseAwareTrait
 */
trait DatabaseAwareTrait
{
    /**
     * Property db.
     *
     * @var  AbstractDatabaseDriver
     */
    protected $db = null;

    /**
     * getDb
     *
     * @return  AbstractDatabaseDriver
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * setDb
     *
     * @param   AbstractDatabaseDriver $db
     *
     * @return  static  Return self to support chaining.
     */
    public function setDb($db)
    {
        $this->db = $db;

        return $this;
    }
}
