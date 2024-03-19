<?php

declare(strict_types=1);

namespace Windwalker\Crypt;

use Throwable;

use function sodium_memzero;
use function str_repeat;

/**
 * The HiddenString class.
 */
class HiddenString
{
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

    public function __destruct()
    {
        if (function_exists('sodium_memzero')) {
            try {
                sodium_memzero($this->value);

                return;
            } catch (Throwable $e) {
                //
            }
        }

        // If sodium not available, attempt to wipe value from memory.
        $zero = str_repeat("\0", mb_strlen($this->value));

        $this->value ^= ($zero ^ $this->value);

        unset($zero, $this->value);
    }
}
