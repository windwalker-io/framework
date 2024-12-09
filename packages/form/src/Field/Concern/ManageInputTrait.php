<?php

declare(strict_types=1);

namespace Windwalker\Form\Field\Concern;

use Windwalker\DOM\HTMLElement;
use Windwalker\Form\FormNormalizer;

/**
 * Trait ManageHTMLTrait
 */
trait ManageInputTrait
{
    public HTMLElement $input;

    /**
     * Use the form renderer to render input.
     *
     * @param  array  $options
     *
     * @return  string
     */
    public function renderInput(array $options = []): string
    {
        $input = $this->getPreparedInput();

        return $this->getForm()->getRenderer()->renderInput($this, $input, $options);
    }

    /**
     * Convert input element as the format that we want exacttly printed.
     *
     * This method is often called in template or before printed.
     * If you got your own renderer or layout for this field, just override this method and return your custom layout.
     *
     * @param  HTMLElement  $input
     * @param  array        $options
     *
     * @return  string|HTMLElement
     */
    public function buildFieldElement(HTMLElement $input, array $options = []): string|HTMLElement
    {
        $surrounds = $this->getSurrounds();

        foreach ($surrounds as $surround) {
            $input = $surround($input, $options);
        }

        return $input;
    }

    /**
     * Prepare the input element attributes.
     *
     * @return  HTMLElement
     *
     * @throws \DOMException
     */
    public function getPreparedInput(): HTMLElement
    {
        $input = clone $this->getInput();

        $input->setAttribute('id', $this->getId());
        $input->setAttribute('name', $this->getInputName());
        $input->setAttribute('data-field-input', true);

        $input = $this->prepareInput($input);

        FormNormalizer::sortAttributes($input);

        return $input;
    }

    /**
     * Prepare the input element attributes, this method is for field customize.
     *
     * @param  HTMLElement  $input
     *
     * @return  HTMLElement
     */
    abstract public function prepareInput(HTMLElement $input): HTMLElement;

    /**
     * @return HTMLElement
     */
    public function getInput(): HTMLElement
    {
        return $this->input;
    }

    /**
     * @param  HTMLElement  $element
     *
     * @return  static  Return self to support chaining.
     */
    public function setInput(HTMLElement $element): static
    {
        $this->input = $element;

        return $this;
    }

    /**
     * getAttribute
     *
     * @param  string  $name
     *
     * @return string|null
     */
    public function getAttribute(string $name): ?string
    {
        $input = $this->getInput();

        if (!$input->hasAttribute($name)) {
            return null;
        }

        return $this->getInput()->getAttribute($name);
    }

    public function hasAttribute(string $name): bool
    {
        $input = $this->getInput();

        return $input->hasAttribute($name);
    }

    public function getAttributes(): array
    {
        return $this->input->getAttributes(true);
    }

    /**
     * getAttribute
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  static
     */
    public function setAttribute(string $name, mixed $value): static
    {
        $this->getInput()->setAttribute($name, $value);

        return $this;
    }

    /**
     * removeAttribute
     *
     * @param  string  $name
     *
     * @return  static
     */
    public function removeAttribute(string $name): static
    {
        $this->getInput()->removeAttribute($name);

        return $this;
    }

    /**
     * attr
     *
     * @param  string  $name
     * @param  mixed   $value
     *
     * @return  static
     */
    public function attr(string $name, mixed $value = null): static
    {
        return $this->setAttribute($name, $value);
    }

    public function getClass(): string
    {
        return $this->getInput()->classList->value;
    }

    public function setClass(string $class): static
    {
        $this->getInput()->setAttribute('class', $class);

        return $this;
    }

    public function addClass(...$args): static
    {
        $this->getInput()->addClass(implode(' ', $args));

        return $this;
    }

    public function removeClass(...$args): static
    {
        $this->getInput()->removeClass(implode(' ', $args));

        return $this;
    }
}
