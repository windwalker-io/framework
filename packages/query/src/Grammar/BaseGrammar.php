<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Grammar;

use LogicException;
use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Query;

/**
 * The BaseGrammar class.
 */
class BaseGrammar extends AbstractGrammar
{
    /**
     * @inheritDoc
     */
    public function listDatabases(): Query
    {
        return $this->createQuery();
    }

    /**
     * @inheritDoc
     */
    public function listTables(?string $schema = null): Query
    {
        return $this->createQuery();
    }

    /**
     * @inheritDoc
     */
    public function listViews(?string $schema = null): Query
    {
        return $this->createQuery();
    }
}
