<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http;

use Windwalker\Utilities\Symbol;
use Windwalker\Utilities\Wrapper\WrapperInterface;

/**
 * The SafeJson class.
 */
class SafeJson implements WrapperInterface
{
    protected mixed $decoded = null;

    public function __construct(
        readonly public string $json,
        protected bool $assoc = false,
        protected int $depth = 512,
        protected int $flags = JSON_THROW_ON_ERROR
    ) {
        $this->decoded = Symbol::empty();
    }

    public function __invoke(mixed $src = null): mixed
    {
        return $this->get();
    }

    /**
     * @return mixed
     */
    public function get(): mixed
    {
        if ($this->decoded === Symbol::empty()) {
            $this->decoded = json_decode($this->json, $this->assoc, $this->depth, $this->flags);
        }

        return $this->decoded;
    }
}
