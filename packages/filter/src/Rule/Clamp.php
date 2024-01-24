<?php

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractFilter;
use Windwalker\Utilities\TypeCast;

use function Windwalker\clamp;

/**
 * The Range class.
 */
class Clamp extends AbstractFilter
{
    protected int|float|null $min;

    protected int|float|null $max;

    /**
     * RangeFilter constructor.
     *
     * @param  int|float|null  $min
     * @param  int|float|null  $max
     */
    public function __construct(int|float|null $min = null, int|float|null $max = null)
    {
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        $value = TypeCast::mustNumeric($value);

        return clamp($value, $this->min, $this->max);
    }
}
