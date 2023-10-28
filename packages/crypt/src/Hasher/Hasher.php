<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Crypt\Hasher;

/**
 * The Hasher class.
 */
class Hasher implements HasherInterface
{
    public function __construct(
        protected string $algo = 'sha256',
        protected bool $binary = false,
        protected array $options = []
    ) {
        //
    }

    public function hash(#[\SensitiveParameter] string $string): string
    {
        return \hash($this->algo, $string, $this->binary, $this->options);
    }

    public function equals(
        #[\SensitiveParameter] string $knownString,
        #[\SensitiveParameter] string $userString,
    ): bool {
        return hash_equals($knownString, $userString);
    }

    public static function algos(): array
    {
        return hash_algos();
    }

    /**
     * @return string
     */
    public function getAlgo(): string
    {
        return $this->algo;
    }

    /**
     * @param  string  $algo
     *
     * @return  static  Return self to support chaining.
     */
    public function setAlgo(string $algo): static
    {
        $this->algo = $algo;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBinary(): bool
    {
        return $this->binary;
    }

    /**
     * @param  bool  $binary
     *
     * @return  static  Return self to support chaining.
     */
    public function setBinary(bool $binary): static
    {
        $this->binary = $binary;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param  array  $options
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }
}
