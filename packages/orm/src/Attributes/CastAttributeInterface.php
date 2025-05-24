<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\Attributes\AttributeInterface;

interface CastAttributeInterface extends AttributeInterface
{
    public const int USE_CONSTRUCTOR = 1 << 0;

    public const int USE_HYDRATOR = 1 << 1;

    public const int NULLABLE = 1 << 2;

    public const int DEFAULT_IF_EMPTY = 1 << 3;

    public const int DEFAULT_IF_NULL = 1 << 4;

    public const int EMPTY_STRING_TO_NULL = 1 << 5;

    /**
     * @return callable|class-string
     */
    public function getHydrate(): mixed;

    /**
     * @return callable|class-string
     */
    public function getExtract(): mixed;

    public function getOptions(): int;
}
