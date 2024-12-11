<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Windwalker\ORM\Cast\JsonCast;
use Windwalker\ORM\Cast\JsonCastArrayList;
use Windwalker\Utilities\TypeCast;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_PROPERTY)]
class JsonArray implements CastAttributeInterface, JsonSerializerInterface
{
    use CastAttributeTrait;

    public function __construct(
        public bool $nullable = false,
        public int $encodeOptions = JSON_THROW_ON_ERROR,
        public int $decodeOptions = JSON_THROW_ON_ERROR,
        protected int $options = 0
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getHydrate(): mixed
    {
        return new JsonCastArrayList(encodeOptions: $this->encodeOptions, decodeOptions: $this->decodeOptions);
    }

    /**
     * @inheritDoc
     */
    public function getExtract(): mixed
    {
        return new JsonCastArrayList(encodeOptions: $this->encodeOptions, decodeOptions: $this->decodeOptions);
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

        if (is_json($data)) {
            return $data;
        }

        return array_values(TypeCast::toArray($data));
    }
}
