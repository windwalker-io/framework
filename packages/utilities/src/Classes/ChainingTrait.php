<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

trait ChainingTrait
{
    /**
     * Pipe a callback and return the result.
     *
     * @param  callable  $callback
     * @param  array     $args
     *
     * @return  static
     */
    public function pipe(callable $callback, ...$args): static
    {
        return $callback($this, ...$args);
    }

    /**
     * Tap a callback and return self.
     *
     * @param  callable  $callback
     * @param  mixed     ...$args
     *
     * @return  static
     */
    public function tap(callable $callback, ...$args): static
    {
        $callback($this, ...$args);

        return $this;
    }

    /**
     * Pipe True/False callback based on a boolean value.
     *
     * @param  mixed          $allow
     * @param  callable|null  $trueCallback
     * @param  callable|null  $falseCallback
     * @param  mixed          ...$args
     *
     * @return  static
     */
    public function pipeIf(
        mixed $allow,
        ?callable $trueCallback = null,
        ?callable $falseCallback = null,
        ...$args
    ): static {
        if ($allow) {
            return $trueCallback ? $trueCallback($this, ...$args) : $this;
        }

        return $falseCallback ? $falseCallback($this, ...$args) : $this;
    }

    /**
     * Tap True/False callback based on a boolean value.
     *
     * @param  mixed          $allow
     * @param  callable|null  $trueCallback
     * @param  callable|null  $falseCallback
     * @param  mixed          ...$args
     *
     * @return  static
     */
    public function tapIf(
        mixed $allow,
        ?callable $trueCallback = null,
        ?callable $falseCallback = null,
        ...$args
    ): static {
        if ($allow) {
            $trueCallback ? $trueCallback($this, ...$args) : null;
        } else {
            $falseCallback ? $falseCallback($this, ...$args) : null;
        }

        return $this;
    }
}
