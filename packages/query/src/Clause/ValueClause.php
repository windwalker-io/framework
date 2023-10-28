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
use Windwalker\Utilities\Wrapper\RawWrapper;

use function Windwalker\unwrap_enum;

/**
 * The ValueCaluse class.
 */
class ValueClause implements ClauseInterface
{
    /**
     * @var string|mixed|Query
     */
    protected mixed $value;

    /**
     * @var string|null
     */
    protected ?string $placeholder = null;

    protected bool $linked = false;

    /**
     * AsClause constructor.
     *
     * @param  string|Query|RawWrapper  $value
     */
    public function __construct(mixed $value)
    {
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
        return $this->placeholder ? ':' . ltrim($this->placeholder, ':') : '?';
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
     * Method to set property placeholder
     *
     * @param  string|null  $placeholder
     *
     * @return  static  Return self to support chaining.
     */
    public function setPlaceholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;

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
}
