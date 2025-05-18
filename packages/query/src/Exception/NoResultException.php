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
    protected static ?\Closure $messageHandler = null;

    public function __construct(
        protected string $table,
        protected mixed $conditions = null,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            "Destination {$this->getTarget()} not found.",
            404,
            $previous
        );

        $this->message = static::renderMessage($this);
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

    protected static function renderMessage(self $e): string
    {
        return static::getMessageHandler()($e);
    }

    public static function getMessageHandler(): \Closure
    {
        return static::$messageHandler ?? static function (self $e) {
            return "Destination {$e->getTarget()} not found.";
        };
    }

    public static function setMessageHandler(?\Closure $messageHandler): void
    {
        static::$messageHandler = $messageHandler;
    }
}
