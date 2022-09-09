<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

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
     * @var string
     */
    protected string $value;

    /**
     * HiddenString constructor.
     *
     * @param  string  $value
     */
    public function __construct(#[\SensitiveParameter] string $value)
    {
        $this->value = CryptHelper::strcpy($value);
    }

    /**
     * Get string back.
     *
     * @return  string
     */
    public function get(): string
    {
        return CryptHelper::strcpy($this->value);
    }

    public static function wrap(#[\SensitiveParameter] mixed $value): HiddenString
    {
        if (!$value instanceof static) {
            $value = new static((string) $value);
        }

        return $value;
    }

    public static function strip(#[\SensitiveParameter] self|string $value): string
    {
        if (!$value instanceof static && PHP_VERSION_ID < 80200) {
            throw new \LogicException(
                'Do not use pure value for encrypting before PHP8.2'
            );
        }

        if ($value instanceof static) {
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
