<?php

declare(strict_types=1);

namespace Windwalker\ORM\Cast;

use JsonException;
use Windwalker\ORM\Attributes\CastAttributeInterface;
use Windwalker\ORM\Attributes\CastAttributeTrait;
use Windwalker\ORM\Attributes\JsonSerializerInterface;
use Windwalker\Utilities\TypeCast;

/**
 * The JsonCast class.
 */
class JsonCast implements CompositeCastInterface
{
    use CompositeCastTrait;

    public const int EMPTY_ARRAY_AS_OBJECT = 1 << 0;

    public const int FORCE_ARRAY_LIST = 1 << 1;

    public function __construct(
        public int $options = self::EMPTY_ARRAY_AS_OBJECT,
        public int $encodeOptions = JSON_THROW_ON_ERROR,
        public int $decodeOptions = JSON_THROW_ON_ERROR,
        public bool $deep = true,
        public bool $nullable = false,
    ) {
        $this->init();
    }

    protected function init(): void
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function hydrate(mixed $value): mixed
    {
        if (!is_string($value)) {
            if ($this->options & static::FORCE_ARRAY_LIST) {
                $value = array_values(TypeCast::toArray($value));
            }

            return $value;
        }

        if ($value === '') {
            return null;
        }

        return json_decode($value, true, 512, $this->decodeOptions) ?: [];
    }

    /**
     * @inheritDoc
     */
    public function extract(mixed $value): mixed
    {
        if (is_json($value)) {
            return $value;
        }

        if ($value === [] && ($this->options & static::EMPTY_ARRAY_AS_OBJECT)) {
            $value = new \stdClass();
        }

        if ($this->options & static::FORCE_ARRAY_LIST) {
            $value = array_values(TypeCast::toArray($value));
        }

        return json_encode($value, $this->encodeOptions);
    }

    public function getOptions(): int
    {
        $options = $this->options;

        if ($this->nullable) {
            $options |= CastAttributeInterface::NULLABLE | CastAttributeInterface::EMPTY_STRING_TO_NULL;
        }

        return $options;
    }

    public function serialize(mixed $data): mixed
    {
        if ($this->nullable && ($data === null || $data === '')) {
            return null;
        }

        if ($this->options & static::FORCE_ARRAY_LIST) {
            return array_values(TypeCast::toArray($data));
        }

        if ($this->options & static::EMPTY_ARRAY_AS_OBJECT) {
            return TypeCast::toObject($data, $this->deep);
        }

        return $data;
    }
}
