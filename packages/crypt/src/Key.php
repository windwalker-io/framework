<?php

declare(strict_types=1);

namespace Windwalker\Crypt;

/**
 * The Index class.
 */
class Key extends HiddenString
{
    public static function wrap(#[\SensitiveParameter] mixed $value): Key
    {
        if (!$value instanceof static) {
            $value = new static((string) $value);
        }

        return $value;
    }

    /**
     * Index is disallow to print as string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return '';
    }
}
