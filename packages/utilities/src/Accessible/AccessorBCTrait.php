<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Accessible;

trait AccessorBCTrait
{
    public function &__call(string $name, array $args)
    {
        if (
            str_starts_with($name, 'get')
            || str_starts_with($name, 'set')
        ) {
            $property = lcfirst(substr($name, 3));

            if (property_exists($this, $property)) {
                if (str_starts_with($name, 'get')) {
                    return $this->{$property};
                }

                if (str_starts_with($name, 'set')) {
                    $this->{$property} = $args[0] ?? null;
                    return $this;
                }
            }
        }

        if (str_starts_with($name, 'is')) {
            $property = lcfirst(substr($name, 2));

            if (property_exists($this, $property)) {
                return $this->{$property};
            }
        }

        throw new \BadMethodCallException(
            sprintf('Method %s::%s() does not exist.', static::class, $name)
        );
    }
}
