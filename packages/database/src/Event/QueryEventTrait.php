<?php

/**
 * Part of sportslottery-certs project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Event;

use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Query\Query;

/**
 * Trait QueryEventTrait
 */
trait QueryEventTrait
{
    protected string $sql = '';

    protected array $bounded = [];

    protected ?StatementInterface $statement = null;

    /**
     * @var mixed
     */
    protected mixed $query = null;

    /**
     * @return mixed
     */
    public function getQuery(): mixed
    {
        return $this->query;
    }

    /**
     * @param  mixed  $query
     *
     * @return  static  Return self to support chaining.
     */
    public function setQuery(mixed $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function getDebugQueryString(): string
    {
        $query = $this->getQuery();

        if ($query instanceof Query) {
            $query = $query->render(true);
        }

        return (string) $query;
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @param  string  $sql
     *
     * @return  static  Return self to support chaining.
     */
    public function setSql(string $sql): static
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * @return array
     */
    public function getBounded(): array
    {
        return $this->bounded;
    }

    /**
     * @param  array  $bounded
     *
     * @return  static  Return self to support chaining.
     */
    public function setBounded(array $bounded): static
    {
        $this->bounded = $bounded;

        return $this;
    }

    /**
     * @return ?StatementInterface
     */
    public function getStatement(): ?StatementInterface
    {
        return $this->statement;
    }

    /**
     * @param  ?StatementInterface  $statement
     *
     * @return  static  Return self to support chaining.
     */
    public function setStatement(?StatementInterface $statement): static
    {
        $this->statement = $statement;

        return $this;
    }
}
