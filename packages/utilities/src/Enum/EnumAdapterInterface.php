<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

/**
 * Interface EnumAdapterInterface
 */
interface EnumAdapterInterface extends \JsonSerializable
{
    public function getValue();
}
