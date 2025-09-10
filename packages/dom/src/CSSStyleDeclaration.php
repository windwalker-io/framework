<?php

declare(strict_types=1);

namespace Windwalker\DOM;

use Windwalker\Utilities\StrNormalize;

class CSSStyleDeclaration
{
    protected string $styleCache = '';

    protected array $properties = [];

    public function __construct(protected HTMLElement $element)
    {
        //
    }

    public function setProperty(string $name, string|\Stringable $value): void
    {
        $properties = $this->getProperties();

        if ($properties[$name] ?? null === $value) {
            return;
        }

        $value = (string) $value;

        if ($value === '') {
            unset($properties[$name]);
        } else {
            $properties[$name] = $value;
        }

        $this->saveStyle($properties);
    }

    public function getPropertyValue(string $name): ?string
    {
        $properties = $this->getProperties();

        return $properties[$name] ?? null;
    }

    public function removeProperty(string $name): void
    {
        $this->setProperty($name, '');
    }

    public function __get(string $name)
    {
        $name = StrNormalize::toKebabCase($name);

        return $this->getPropertyValue($name);
    }

    public function __set(string $name, string|\Stringable $value): void
    {
        $name = StrNormalize::toKebabCase($name);

        $this->setProperty($name, (string) $value);
    }

    public function __isset(string $name): bool
    {
        $name = StrNormalize::toKebabCase($name);

        return $this->getPropertyValue($name) !== null;
    }

    public function __unset(string $name): void
    {
        $name = StrNormalize::toKebabCase($name);

        $this->removeProperty($name);
    }

    public function __toString(): string
    {
        return (string) $this->element->getAttribute('style');
    }

    protected function saveStyle(array $properties): void
    {
        $css = static::toCssText($properties);

        $this->element->setAttribute('style', $css);
    }

    protected static function toCssText(array $properties): string
    {
        $rules = [];

        foreach ($properties as $name => $value) {
            $rules[] = $name . ': ' . $value;
        }

        return implode('; ', $rules);
    }

    protected function getProperties(): array
    {
        $style = (string) $this->element->getAttribute('style');

        if ($style === $this->styleCache) {
            return $this->properties;
        }

        $this->styleCache = $style;

        $properties = [];
        $rules = explode(";", (string) ($this->element->getAttribute('style') ?? ''));

        foreach ($rules as $rule) {
            $rule = trim($rule);

            if ($rule === "") {
                continue;
            }

            [$name, $value] = array_map("trim", explode(':', $rule, 2));

            $properties[strtolower($name)] = $value;
        }

        return $this->properties = $properties;
    }
}
