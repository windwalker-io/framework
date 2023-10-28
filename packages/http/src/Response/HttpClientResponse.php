<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Response;

/**
 * The HttpClientResponse class.
 */
class HttpClientResponse extends Response
{
    protected mixed $info;

    public function withInfo(mixed $info): static
    {
        $new = clone $this;
        $new->info = $info;

        return $new;
    }

    public function getInfo(): mixed
    {
        return $this->info;
    }
}
