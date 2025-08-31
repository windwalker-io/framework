<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Wrapper;

class DepthWrapper implements WrapperInterface
{
    public function __construct(public int $depth = 5)
    {
    }

    public function __invoke(mixed $src = null): int
    {
        return $this->depth;
    }
}
