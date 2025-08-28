<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Options;

/**
 * @deprecated  Only for B/C use.
 */
class RecordOptions implements \ArrayAccess
{
    use RecordOptionsTrait;

    public function offsetExists(mixed $offset): bool
    {
        $offset = $this->normalizeKey($offset);

        return isset($this->$offset);
    }

    public function &offsetGet(mixed $offset): mixed
    {
        $offset = $this->normalizeKey($offset);

        return $this->$offset;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $offset = $this->normalizeKey($offset);
        $this->$offset = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        $offset = $this->normalizeKey($offset);
        unset($this->$offset);
    }
}
