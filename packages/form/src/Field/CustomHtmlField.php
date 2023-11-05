<?php

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\DOM\DOMElement;

/**
 * The ButtonField class.
 *
 * @since  2.1.8
 */
class CustomHtmlField extends AbstractField
{
    protected mixed $content = '';

    /**
     * buildInput
     *
     * @param  DOMElement  $input
     * @param  array       $options
     *
     * @return  string|DOMElement
     */
    public function buildFieldElement(DOMElement $input, array $options = []): string|DOMElement
    {
        $content = $this->getContent();

        if (is_callable($content)) {
            return $content($this, $input->getAttributes(true), $options);
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    public function prepareInput(DOMElement $input): DOMElement
    {
        return $input;
    }

    /**
     * @return mixed
     */
    public function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * @param  string|DOMElement|callable  $content
     *
     * @return  static  Return self to support chaining.
     */
    public function content(mixed $content): static
    {
        $this->content = $content;

        return $this;
    }
}
