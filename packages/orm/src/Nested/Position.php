<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\ORM\Nested;

/**
 * The Position class.
 */
class Position
{
    public const MOVE_UP = -1;

    public const MOVE_DOWN = 1;

    public const BEFORE = NestedPosition::BEFORE;

    public const AFTER = NestedPosition::AFTER;

    public const FIRST_CHILD = NestedPosition::FIRST_CHILD;

    public const LAST_CHILD = NestedPosition::LAST_CHILD;

    /**
     * Position constructor.
     *
     * @param  mixed           $referenceId
     * @param  NestedPosition  $position
     */
    public function __construct(
        protected mixed $referenceId = null,
        protected NestedPosition $position = self::LAST_CHILD
    ) {
        //
    }

    /**
     * @return mixed
     */
    public function getReferenceId(): mixed
    {
        return $this->referenceId;
    }

    /**
     * @param  mixed  $referenceId
     *
     * @return  static  Return self to support chaining.
     */
    public function setReferenceId(mixed $referenceId): static
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * @return NestedPosition
     */
    public function getPosition(): NestedPosition
    {
        return $this->position;
    }

    /**
     * @param  NestedPosition  $position
     *
     * @return  static  Return self to support chaining.
     */
    public function setPosition(NestedPosition $position): static
    {
        $this->position = $position;

        return $this;
    }
}
