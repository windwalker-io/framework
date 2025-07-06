<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

/**
 * Interface EnumAdapterInterface
 *
 * @deprecated  Use 8.2 enums
 */
interface EnumAdapterInterface extends \JsonSerializable
{
    public function getValue();
}
