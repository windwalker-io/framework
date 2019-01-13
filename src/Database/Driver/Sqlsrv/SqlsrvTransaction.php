<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver\Sqlsrv;

use Windwalker\Database\Driver\Pdo\PdoTransaction;

/**
 * The SqlsrvTransaction class.
 *
 * @since  3.5
 */
class SqlsrvTransaction extends PdoTransaction
{
    /**
     * start
     *
     * @return  static
     */
    public function start()
    {
        if (!$this->nested || !$this->depth) {
            parent::start();
        } else {
            $savepoint = 'SP_' . $this->depth;
            $this->db->setQuery('SAVE TRANSACTION ' . $this->db->quoteName($savepoint));

            if ($this->db->execute()) {
                $this->depth++;
            }
        }

        return $this;
    }

    /**
     * commit
     *
     * @return  static
     */
    public function commit()
    {
        if (!$this->nested || $this->depth <= 1) {
            parent::commit();
        } else {
            $this->depth--;
        }

        return $this;
    }

    /**
     * rollback
     *
     * @return  static
     */
    public function rollback()
    {
        if (!$this->nested || $this->depth <= 1) {
            parent::rollback();
        } else {
            $savepoint = 'SP_' . ($this->depth - 1);
            $this->db->setQuery('ROLLBACK TRANSACTION ' . $this->db->quoteName($savepoint));

            if ($this->db->execute()) {
                $this->depth--;
            }
        }

        return $this;
    }
}
