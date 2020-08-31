<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

use ArrayAccess;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Contract\AccessibleInterface;
use Windwalker\Utilities\TypeCast;

/**
 * The OptionAccessTrait class.
 *
 * @since  3.0.1
 */
trait StateAccessTrait
{
    protected array|ArrayAccess|AccessibleInterface $stateStorage = [];

    /**
     * prepareDefaultOptions
     *
     * @param  array  $defaults
     * @param  array  $options
     *
     * @return  void
     */
    protected function prepareState(array $defaults = [], array $options = []): void
    {
        $this->stateStorage = Arr::mergeRecursive(TypeCast::toArray($this->stateStorage), $defaults, $options);
    }

    public function getState(string $name, $default = null)
    {
        return $this->stateStorage[$name] ?? $default;
    }

    public function setState(string $name, $value): static
    {
        $this->stateStorage[$name] = $value;

        return $this;
    }

    public function getStates(): array|ArrayAccess|AccessibleInterface
    {
        return $this->stateStorage;
    }

    public function setStates(array|ArrayAccess|AccessibleInterface $state)
    {
        $this->stateStorage = $state;

        return $this;
    }
}
