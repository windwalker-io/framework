<?php

declare(strict_types=1);

namespace Windwalker\Data;

use Windwalker\Utilities\Contract\ArrayAccessibleInterface;

interface ValueObjectInterface extends
    RecordInterface,
    ArrayAccessibleInterface,
    \IteratorAggregate,
    \Stringable
{
}
