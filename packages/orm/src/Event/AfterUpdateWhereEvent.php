<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Event;

use Attribute;
use Windwalker\Database\Driver\StatementInterface;

/**
 * The AfterUpdateBatchEvent class.
 */
#[Attribute]
class AfterUpdateWhereEvent extends AbstractUpdateWhereEvent
{
    protected StatementInterface $statement;

    /**
     * @return StatementInterface
     */
    public function getStatement(): StatementInterface
    {
        return $this->statement;
    }

    /**
     * @param  StatementInterface  $statement
     *
     * @return  static  Return self to support chaining.
     */
    public function setStatement(StatementInterface $statement): static
    {
        $this->statement = $statement;

        return $this;
    }
}
