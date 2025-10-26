<?php

declare(strict_types=1);

namespace Windwalker\Data;

use Windwalker\Utilities\Contract\ArrayAccessibleInterface;
use Windwalker\Utilities\Contract\DumpableInterface;

interface RecordInterface extends DumpableInterface, \JsonSerializable
{
    public static function wrapWith(...$values): static;

    public static function tryWrapWith(...$data): ?static;

    public static function wrap(mixed $values): static;

    public static function tryWrap(mixed $data): ?static;

    public function fill(mixed $data): static;

    public function with(...$data): static;

    /**
     * @template T
     *
     * @param  class-string<T>  $className
     *
     * @return T
     */
    public function as(string $className): object;
}
