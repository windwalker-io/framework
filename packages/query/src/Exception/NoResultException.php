<?php

declare(strict_types=1);

namespace Windwalker\Query\Exception;

use Throwable;
use Windwalker\Utilities\StrInflector;
use Windwalker\Utilities\StrNormalize;

/**
 * The NoResultException class.
 */
class NoResultException extends \UnexpectedValueException
{
    public function __construct(
        protected string $table,
        protected mixed $conditions = null,
        Throwable $previous = null
    ) {
        parent::__construct(
            "Destination {$this->getTarget()} not found.",
            404,
            $previous
        );
    }

    public function getTarget(): string
    {
        return StrInflector::toSingular(
            StrNormalize::toPascalCase($this->getTable())
        );
    }

    public function getFullMessage(): string
    {
        return sprintf(
            'Unable to find result of %s in: %s',
            $this->getConditionsString(),
            $this->getTable()
        );
    }

    /**
     * @return mixed
     */
    public function getConditions(): mixed
    {
        return $this->conditions;
    }

    public function getConditionsString(): string
    {
        return json_encode($this->getConditions());
    }

    /**
     * @return mixed
     */
    public function getTable(): string
    {
        return $this->table;
    }
}
