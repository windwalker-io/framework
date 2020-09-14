<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Options;

use ArrayAccess;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Contract\AccessibleInterface;
use Windwalker\Utilities\TypeCast;

/**
 * The OptionAccessTrait class.
 *
 * @since  3.0.1
 */
trait OptionAccessTrait
{
    protected array|ArrayAccess|AccessibleInterface $options = [];

    /**
     * prepareDefaultOptions
     *
     * @param  array  $defaults
     * @param  array  $options
     *
     * @return  void
     */
    protected function prepareOptions(array $defaults = [], array $options = []): void
    {
        $this->options = Arr::mergeRecursive(TypeCast::toArray($this->options), $defaults, $options);
    }

    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    public function setOption(string $name, $value): static
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function getOptions(): array|ArrayAccess|AccessibleInterface
    {
        return $this->options;
    }

    public function setOptions(array|ArrayAccess|AccessibleInterface $options)
    {
        $this->options = $options;

        return $this;
    }
}
