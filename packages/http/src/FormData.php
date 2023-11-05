<?php

declare(strict_types=1);

namespace Windwalker\Http;

use Windwalker\Data\Collection;
use Windwalker\Uri\UriHelper;
use Windwalker\Utilities\Accessible\AccessibleTrait;
use Windwalker\Utilities\TypeCast;

/**
 * The FormData class.
 */
class FormData implements \JsonSerializable
{
    use AccessibleTrait;

    public function __construct(mixed $data = null)
    {
        if ($data !== null) {
            foreach (TypeCast::toArray($data) as $k => $v) {
                $this->set($k, $v);
            }
        }
    }

    public static function create(mixed $data = null): static
    {
        return new static($data);
    }

    public static function wrap(mixed $data = null): static
    {
        if ($data instanceof self) {
            return $data;
        }

        return static::create($data);
    }

    public function __toString(): string
    {
        return UriHelper::buildQuery($this->storage);
    }

    public function all(): array
    {
        return $this->storage;
    }
}
