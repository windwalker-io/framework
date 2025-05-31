<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Accessible;

trait AccessorBCTrait
{
    /**
     * @param  string  $name
     * @param  array   $args
     *
     * @return  $this
     *
     * @deprecated  Use property instead.
     */
    public function __call(string $name, array $args)
    {
        if (
            str_starts_with($name, 'get')
            || str_starts_with($name, 'set')
        ) {
            $property = substr($name, 3);

            $ref = new \ReflectionClass($this);
            $props = $ref->getProperties(
                \ReflectionProperty::IS_PUBLIC
                | \ReflectionProperty::IS_PROTECTED
                | \ReflectionProperty::IS_PRIVATE
            );

            foreach ($props as $propRef) {
                if (strcasecmp($propRef->getName(), $property) === 0) {
                    if (str_starts_with($name, 'get')) {
                        return $propRef->getValue($this);
                    }

                    if (str_starts_with($name, 'set')) {
                        $propRef->setValue($this, $args[0] ?? null);
                        return $this;
                    }
                }
            }
        }

        if (str_starts_with($name, 'is')) {
            $property = substr($name, 2);

            $ref = new \ReflectionClass($this);
            $props = $ref->getProperties(
                \ReflectionProperty::IS_PUBLIC
                | \ReflectionProperty::IS_PROTECTED
                | \ReflectionProperty::IS_PRIVATE
            );

            foreach ($props as $propRef) {
                if (
                    strcasecmp($propRef->getName(), $property) === 0
                    || strcasecmp($propRef->getName(), $name) === 0
                ) {
                    return $propRef->getValue($this);
                }
            }
        }

        throw new \BadMethodCallException(
            sprintf('Method %s::%s() does not exist.', static::class, $name)
        );
    }
}
