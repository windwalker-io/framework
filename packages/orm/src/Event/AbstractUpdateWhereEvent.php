<?php

declare(strict_types=1);

namespace Windwalker\ORM\Event;

/**
 * The AbstractUpdateBatchEvent class.
 */
abstract class AbstractUpdateWhereEvent extends AbstractEntityEvent
{
    protected mixed $conditions;

    protected int $options = 0;

    protected array|object $source = [];

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
     * @return array|object
     */
    public function getSource(): object|array
    {
        return $this->source;
    }

    /**
     * @param  array|object  $source
     *
     * @return  static  Return self to support chaining.
     */
    public function setSource(object|array $source): static
    {
        $this->source = $source;

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
