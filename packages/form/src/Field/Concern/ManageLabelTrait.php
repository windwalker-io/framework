<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field\Concern;

use Windwalker\DOM\DOMElement;
use Windwalker\Form\FormNormalizer;

/**
 * Trait ManageLabelTrait
 */
trait ManageLabelTrait
{
    public DOMElement $label;

    /**
     * label
     *
     * @param  string  $label
     *
     * @return  static
     */
    public function label(string $label): static
    {
        $this->getLabel()->textContent = $label;

        return $this;
    }

    /**
     * getLabel
     *
     * @param  array  $options
     *
     * @return  string
     */
    public function renderLabel(array $options = []): string
    {
        $label = $this->getPreparedLabel();

        return $this->getForm()->getRenderer()->renderLabel($this, $label, $options);
    }

    public function buildLabel(DOMElement $label, array $options = []): string|DOMElement
    {
        return $label;
    }

    public function getPreparedLabel(): DOMElement
    {
        $label = clone $this->getLabel();

        $label->setAttribute('id', $this->getId('-label'));
        $label->setAttribute('for', $this->getId());
        $label->setAttribute('data-field-label', true);

        if ($this->getDescription()) {
            $label->setAttribute('title', $this->getDescription());
        }

        FormNormalizer::sortAttributes($label);

        return $label;
    }

    public function modifyLabel(callable $handler): static
    {
        $handler($this->getLabel());

        return $this;
    }

    /**
     * getLabel
     *
     * @return  DOMElement
     */
    public function getLabel(): DOMElement
    {
        return $this->label;
    }

    public function getLabelName(): string
    {
        return (string) $this->getLabel()->textContent;
    }

    /**
     * @param  DOMElement  $label
     *
     * @return  static  Return self to support chaining.
     */
    public function setLabel(DOMElement $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * getAttribute
     *
     * @param  string  $name
     *
     * @return string|null
     */
    public function getLabelAttribute(string $name): ?string
    {
        if (!$this->hasLabelAttribute($name)) {
            return null;
        }

        return $this->getLabel()->getAttribute($name);
    }

    public function hasLabelAttribute(string $name): bool
    {
        return $this->getLabel()->hasAttribute($name);
    }

    /**
     * getAttribute
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  static
     * @throws \DOMException
     */
    public function setLabelAttribute(string $name, string $value): static
    {
        $this->getLabel()->setAttribute($name, $value);

        return $this;
    }

    public function removeLabelAttribute(string $name): static
    {
        $this->getLabel()->removeAttribute($name);

        return $this;
    }

    public function getLabelAttributes(): array
    {
        return $this->getLabel()->getAttributes(true);
    }

    /**
     * attr
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  static
     * @throws \DOMException
     */
    public function labelAttr(string $name, mixed $value = null): static
    {
        $this->getLabel()->setAttribute($name, $value);

        return $this;
    }

    public function getLabelClass(): string
    {
        return $this->getLabel()->classList->value;
    }

    public function setLabelClass(string $class): static
    {
        $this->getLabel()->setAttribute('class', $class);

        return $this;
    }

    public function addLabelClass(...$args): static
    {
        $this->getLabel()->classList->add(...$args);

        return $this;
    }

    public function removeLabelClass(...$args): static
    {
        $this->getLabel()->classList->remove(...$args);

        return $this;
    }
}
