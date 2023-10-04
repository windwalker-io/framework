<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Clause;

use Windwalker\Query\Query;

/**
 * The QuoteNameClause class.
 */
class QuoteNameClause implements ClauseInterface
{
    protected string|Clause|null $quoted = null;

    /**
     * QuoteNameClause constructor.
     *
     * @param  mixed       $value
     * @param  Query|null  $query
     */
    public function __construct(
        protected mixed $value,
        protected ?Query $query = null
    ) {
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param  mixed  $value
     *
     * @return  static  Return self to support chaining.
     */
    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return Query|null
     */
    public function getQuery(): ?Query
    {
        return $this->query;
    }

    /**
     * @param  Query  $query
     *
     * @return  static  Return self to support chaining.
     */
    public function setQuery(Query $query): static
    {
        $this->query = $query;

        $this->doQuoteName();

        return $this;
    }

    protected function doQuoteName(): mixed
    {
        if (!$this->query) {
            return $this->value;
        }

        return $this->quoted ??= $this->query->resolveColumn((string) $this->value);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return (string) $this->doQuoteName();
    }
}
