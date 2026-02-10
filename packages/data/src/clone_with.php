<?php

declare(strict_types=1);

namespace Windwalker\Data {
    function cloneWithPolyfill(object $object, array $data): object
    {
        $fun = fn (array $data) => clone($object, $data);
        $fun = $fun->bindTo($object, $object);

        return $fun($data);
    }
}
