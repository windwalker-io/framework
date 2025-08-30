<?php

declare(strict_types=1);

namespace Windwalker\ORM\Metadata;

use Windwalker\ORM\Attributes\Column;

/**
 * @internal
 */
class EntityMember
{
    public string $memberName {
        get => $this->memberRef->getName();
    }

    public string $columnName {
        get => $this->column->getName();
    }

    public bool $isProperty {
        get => $this->memberRef instanceof \ReflectionProperty;
    }

    public bool $isMethod {
        get => $this->memberRef instanceof \ReflectionMethod;
    }

    public array $attributes = [];

    public function __construct(
        public readonly \ReflectionProperty|\ReflectionMethod $memberRef,
        public ?Column $column = null,
    ) {
    }

    public function addAttribute(object $attr): static
    {
        $this->attributes[] = $attr;

        return $this;
    }

    public function hasAttribute(string $attrName): bool
    {
        return array_any($this->attributes, fn($attr) => $attr instanceof $attrName);
    }

    public function getAttribute(string $attrName): ?object
    {
        return array_find($this->attributes, fn($attribute) => $attribute instanceof $attrName);
    }
}
