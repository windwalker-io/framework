<?php

declare(strict_types=1);

namespace Windwalker\Http;

interface FullPathAwareInterface
{
    public function getFullPath(): ?string;
}
