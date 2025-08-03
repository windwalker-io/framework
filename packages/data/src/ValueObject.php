<?php

declare(strict_types=1);

namespace Windwalker\Data;

use IteratorAggregate;
use Windwalker\Attributes\AttributesAccessor;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\CastNullable;
use Windwalker\Utilities\Contract\ArrayAccessibleInterface;
use Windwalker\Utilities\Contract\DumpableInterface;
use Windwalker\Utilities\StrNormalize;
use Windwalker\Utilities\TypeCast;

/**
 * The ValueObject class.
 */
#[\AllowDynamicProperties]
class ValueObject implements ValueObjectInterface
{
    use ValueObjectTrait;

    public function __construct(mixed $data = null)
    {
        $this->fill($data);
    }

    public static function wrap(mixed $data): static
    {
        if ($data instanceof static) {
            return $data;
        }

        $ref = new \ReflectionClass(static::class);
        $method = $ref->getConstructor();

        if ($method?->getDeclaringClass()->getName() !== static::class) {
            // The final class declares a constructor, so we will use it.
            return new static()->fill($data);
        }

        // Back to legacy constructor.
        return new static($data);
    }
}
