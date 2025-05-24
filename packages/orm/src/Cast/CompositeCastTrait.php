<?php

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

use Windwalker\ORM\Attributes\CastAttributeTrait;

trait CompositeCastTrait
{
    use CastAttributeTrait;

    /**
     * @inheritDoc
     */
    public function getHydrate(): mixed
    {
        return $this->hydrate(...);
    }

    /**
     * @inheritDoc
     */
    public function getExtract(): mixed
    {
        return $this->extract(...);
    }

    public function getOptions(): int
    {
        return 0;
    }
}
