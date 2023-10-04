<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Event;

/**
 * The AbstractDeleteEvent class.
 */
abstract class AbstractDeleteEvent extends AbstractEntityEvent
{
    protected mixed $conditions;

    protected int $options = 0;

    protected ?object $entity = null;

    /**
     * @return mixed
     */
    public function &getConditions(): mixed
    {
        return $this->conditions;
    }

    /**
     * @param  mixed  $conditions
     *
     * @return  static  Return self to support chaining.
     */
    public function setConditions(mixed $conditions): static
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return object|null
     */
    public function getEntity(): ?object
    {
        return $this->entity;
    }

    /**
     * @param  object|null  $entity
     *
     * @return  static  Return self to support chaining.
     */
    public function setEntity(?object $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return int
     */
    public function &getOptions(): int
    {
        return $this->options;
    }

    /**
     * @param  int  $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(int $options): static
    {
        $this->options = $options;

        return $this;
    }
}
