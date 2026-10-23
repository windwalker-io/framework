<?php

declare(strict_types=1);

namespace Windwalker\Crypt;

use LogicException;
use Throwable;

use function sodium_memzero;
use function str_repeat;

/**
 * The HiddenString class.
 */
class HiddenString
{
    protected bool $copyLegacy = false;

    /**
     * HiddenString constructor.
     *
     * @param  string  $value
     */
    public function __construct(
        #[\SensitiveParameter] protected string $value,
        protected bool $copy = true
    ) {
        //
    }

    /**
     * Get string back.
     *
     * @return  string
     */
    public function get(?bool $copy = null): string
    {
        if ($copy === null) {
            $copy = $this->copy;
        }

        if ($copy) {
            if ($this->copyLegacy) {
                return CryptHelper::strcpyLegacy($this->value);
            }

            return CryptHelper::strcpy($this->value);
        }

        return $this->value;
    }

    public static function wrap(#[\SensitiveParameter] mixed $value, bool $copy = true): HiddenString
    {
        if (!$value instanceof self) {
            $value = new static((string) $value, $copy);
        }

        return $value;
    }

    public static function strip(#[\SensitiveParameter] self|string $value): string
    {
        if ($value instanceof self) {
            $value = $value->get();
        }

        return $value;
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->get();
    }

    public function __debugInfo(): array
    {
        return ['value' => '[redacted]'];
    }

    public function __serialize(): array
    {
        throw new LogicException('Secret values cannot be serialized.');
    }

    public function __unserialize(array $data): void
    {
        throw new LogicException('Secret values cannot be unserialized.');
    }

    public function __destruct()
    {
        if (!isset($this->value)) {
            return;
        }

        if (function_exists('sodium_memzero')) {
            try {
                sodium_memzero($this->value);

                return;
            } catch (Throwable $e) {
                //
            }
        }

        // If sodium not available, attempt to wipe value from memory.
        $zero = str_repeat("\0", strlen($this->value));

        $this->value ^= ($zero ^ $this->value);

        unset($zero, $this->value);
    }

    public function useLegacyCopy(bool $copyLegacy = true): static
    {
        $this->copyLegacy = $copyLegacy;

        return $this;
    }
}
