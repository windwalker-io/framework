<?php

declare(strict_types=1);

namespace Windwalker\Scalars\Concern;

/**
 * The StringDeprecatedTrait contains backward-compatible proxy methods
 * that have been renamed. Each method delegates to its replacement.
 */
trait StringDeprecatedTrait
{
    /**
     * @deprecated Use ensureStart() instead.
     */
    public function ensureLeft(string $search): static
    {
        return $this->ensureStart($search);
    }

    /**
     * @deprecated Use ensureEnd() instead.
     */
    public function ensureRight(string $search): static
    {
        return $this->ensureEnd($search);
    }

    /**
     * @deprecated Use removeStart() instead.
     */
    public function removeLeft(string $search): static
    {
        return $this->removeStart($search);
    }

    /**
     * @deprecated Use removeEnd() instead.
     */
    public function removeRight(string $search): static
    {
        return $this->removeEnd($search);
    }

    /**
     * @deprecated Use intersectStart() instead.
     */
    public function intersectLeft(string $string2): static
    {
        return $this->intersectStart($string2);
    }

    /**
     * @deprecated Use intersectEnd() instead.
     */
    public function intersectRight(string $string2): static
    {
        return $this->intersectEnd($string2);
    }

    /**
     * @deprecated Use padStart() instead.
     */
    public function padLeft(int $length = 0, string $substring = ' '): static
    {
        return $this->padStart($length, $substring);
    }

    /**
     * @deprecated Use padEnd() instead.
     */
    public function padRight(int $length = 0, string $substring = ' '): static
    {
        return $this->padEnd($length, $substring);
    }
}

