<?php

declare(strict_types=1);

namespace Windwalker\Data;

use Windwalker\Utilities\Contract\ArrayAccessibleInterface;
use Windwalker\Utilities\Contract\DumpableInterface;

interface ValueObjectInterface extends
    ArrayAccessibleInterface,
    DumpableInterface,
    \IteratorAggregate,
    \Stringable,
    \JsonSerializable
{
    public static function wrapWith(...$data): static;

    public static function tryWrapWith(...$data): ?static;

    public static function wrap(mixed $data): static;

    public static function tryWrap(mixed $data): ?static;

    public function fill(mixed $data): static;

    public function fillWith(...$data): static;

    /**
     * @template T
     *
     * @param  class-string<T>  $className
     *
     * @return T
     */
    public function as(string $className): object;
}
