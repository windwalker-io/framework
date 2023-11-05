<?php

declare(strict_types=1);

namespace Windwalker\ORM\Relation;

/**
 * The Action class.
 */
class Action
{
    /**
     * Delete or update the row from the parent table, and automatically delete or update the matching rows
     * in the child table.
     *
     * @const  string
     */
    public const CASCADE = 'CASCADE';

    /**
     * Rejects the delete or update operation for the parent table.
     *
     * @const  string
     *
     * @deprecated Use RESTRICT instead.
     */
    public const NO_ACTION = 'RESTRICT';

    /**
     * Rejects the delete or update operation for the parent table.
     *
     * Same as NO_ACTION.
     *
     * @const  string
     */
    public const RESTRICT = 'RESTRICT';

    /**
     * No any actions on delete or updates.
     *
     * This is not SQL standard actions, just tell ORM tht ignore relations.
     *
     * @const  string
     */
    public const IGNORE = 'IGNORE';

    /**
     * Delete or update the row from the parent table, and set the foreign key column or columns in the child table to
     * NULL.
     *
     * @const  string
     */
    public const SET_NULL = 'SET NULL';

    public const ACTIONS = [
        self::SET_NULL,
        self::CASCADE,
        self::RESTRICT,
        self::IGNORE,
    ];
}
