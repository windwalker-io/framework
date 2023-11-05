<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Contract;

use JetBrains\PhpStorm\ArrayShape;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * Interface LanguageInterface
 */
interface LanguageInterface
{
    /**
     * translate
     *
     * @param  string       $id
     * @param  string|null  $locale
     * @param  bool         $fallback
     *
     * @return  array
     */
    #[ArrayShape(['string', 'string'])]
    public function get(
        string $id,
        ?string $locale = null,
        bool $fallback = true
    ): array;

    public function trans(string|RawWrapper $id, ...$args): string;

    public function choice(string|RawWrapper $id, int|float $number, ...$args);

    public function replace(string $string, array $args = []): string;

    /**
     * has
     *
     * @param  string       $id
     * @param  string|null  $locale
     * @param  bool         $fallback
     *
     * @return  bool
     *
     * @since  3.5.2
     */
    public function has(string $id, ?string $locale = null, bool $fallback = true): bool;
}
