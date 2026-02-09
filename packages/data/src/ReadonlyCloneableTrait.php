<?php

declare(strict_types=1);

namespace Windwalker\Data;

trait ReadonlyCloneableTrait
{
    public function cloneWith(array $data): static
    {
        $ref = new \ReflectionClass($this);
        $new = $ref->newInstanceWithoutConstructor();

        foreach ($ref->getProperties() as $property) {
            if (array_key_exists($property->getName(), $data)) {
                $property->setValue($new, $data[$property->getName()]);
            } else {
                $property->setValue($new, $property->getValue($this));
            }
        }

        return $new;
    }
}
