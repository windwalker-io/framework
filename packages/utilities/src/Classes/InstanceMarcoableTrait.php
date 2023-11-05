<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

use BadMethodCallException;
use Closure;

/**
 * The InstanceMarcoableTrait class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait InstanceMarcoableTrait
{
    /**
     * @var  callable[]
     */
    protected array $macros = [];

    public function macro(string $name, callable $macro): static
    {
        $this->macros[$name] = $macro;

        return $this;
    }

    public function do(string $name, ...$args): mixed
    {
        return $this->__call($name, $args);
    }

    public function hasMacro(string $name): bool
    {
        return isset($this->macros[$name]);
    }

    public function clearMarco(): static
    {
        $this->macros = [];

        return $this;
    }

    public function __call(string $name, array $args): mixed
    {
        if (!$this->hasMacro($name)) {
            throw new BadMethodCallException(
                sprintf(
                    'Method %s::%s does not exist.',
                    static::class,
                    $name
                )
            );
        }

        $macro = $this->macros[$name];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, static::class);
        }

        return $macro(...$args);
    }
}
