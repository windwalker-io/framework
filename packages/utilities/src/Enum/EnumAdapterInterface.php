<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

/**
 * Interface EnumAdapterInterface
 */
interface EnumAdapterInterface extends \JsonSerializable
{
    public function getValue();
}