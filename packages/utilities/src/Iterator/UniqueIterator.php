<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Iterator;

use FilterIterator;
use Iterator;

/**
 * The UniqueIterator class.
 */
class UniqueIterator extends FilterIterator
{
    /**
     * @var array
     */
    protected array $exists = [];

    /**
     * @var int
     */
    protected int $flags = SORT_STRING;

    /**
     * @inheritDoc
     */
    public function __construct(Iterator $iterator, int $flags = SORT_STRING)
    {
        parent::__construct($iterator);

        $this->flags = $flags;
    }

    /**
     * @inheritDoc
     */
    public function accept(): bool
    {
        $current = $this->current();

        $result = !in_array($this->formatValue($current), $this->exists);

        if ($result) {
            $this->exists[] = $current;
        }

        return $result;
    }

    /**
     * formatValue
     *
     * @param  mixed  $value
     *
     * @return  mixed
     */
    protected function formatValue(mixed $value): mixed
    {
        return match ($this->flags) {
            SORT_NUMERIC => (float) $value,
            SORT_STRING, SORT_LOCALE_STRING => (string) $value,
            default => $value,
        };
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->exists = [];

        parent::rewind();
    }

    /**
     * Method to set property strict
     *
     * @param  int  $flags
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setFlags(int $flags): static
    {
        $this->flags = $flags;

        return $this;
    }
}
