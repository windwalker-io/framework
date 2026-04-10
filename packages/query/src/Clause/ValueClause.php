<?php

declare(strict_types=1);

namespace Windwalker\Query\Clause;

use Windwalker\Query\Query;
use Windwalker\Utilities\Wrapper\RawWrapper;

use function Windwalker\unwrap_enum;

/**
 * The ValueClause class.
 *
 * @internal
 */
class ValueClause implements ClauseInterface
{
    public static ?\Closure $idHandler = null;

    /**
     * @var string|mixed|Query
     */
    protected mixed $value;

    /**
     * @var string|null
     */
    public protected(set) ?string $prefix = 'wqp__';

    protected bool $linked = false;

    public int $id {
        get => $this->id ??= static::getIdHandler()();
    }

    /**
     * AsClause constructor.
     *
     * @param  string|Query|RawWrapper  $value
     */
    public function __construct(mixed $value)
    {
        // $this->id = ++static::$serial;
        $this->value = unwrap_enum($value);
    }

    public function __toString(): string
    {
        $value = $this->value;

        if ($value instanceof RawWrapper) {
            return (string) $value();
        }

        if ($value instanceof Query) {
            return '(' . $value . ')';
        }

        $this->setLinked(true);

        return $this->getPlaceholder();
    }

    public function getPlaceholder(): string
    {
        return $this->prefix ? ':' . $this->prefix . $this->id : '?';
    }

    /**
     * Method to get property Column
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function &getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Method to set property column
     *
     * @param  mixed  $value
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function value(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLinked(): bool
    {
        return $this->linked;
    }

    /**
     * @param  bool  $linked
     *
     * @return  static  Return self to support chaining.
     */
    public function setLinked(bool $linked): static
    {
        $this->linked = $linked;

        return $this;
    }

    public function setPrefix(?string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    protected static function getIdHandler(): \Closure
    {
        return static::$idHandler ??= static function (ValueClause $clause) {
            return spl_object_id($clause);
        };
    }
}
