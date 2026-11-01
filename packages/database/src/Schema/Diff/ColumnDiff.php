<?php

declare(strict_types=1);

namespace Windwalker\Database\Schema\Diff;

use Windwalker\Database\Schema\Ddl\Column;

/**
 * A modified column: the desired definition, the actual one and the changed properties.
 */
class ColumnDiff
{
    /**
     * @param  Column                                        $desired
     * @param  Column                                        $actual
     * @param  array<string, array{from: mixed, to: mixed}>  $changes
     */
    public function __construct(
        public readonly Column $desired,
        public readonly Column $actual,
        public readonly array $changes,
    ) {}

    public string $name {
        get => $this->desired->getColumnName();
    }
}
