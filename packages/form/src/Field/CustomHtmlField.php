<?php

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\DOM\HTMLElement;

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
     * @param  HTMLElement  $input
     * @param  array       $options
     *
     * @return  string|HTMLElement
     */
    public function buildFieldElement(HTMLElement $input, array $options = []): string|HTMLElement
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
    public function prepareInput(HTMLElement $input): HTMLElement
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
     * @param  string|HTMLElement|callable  $content
     *
     * @return  static  Return self to support chaining.
     */
    public function content(mixed $content): static
    {
        $this->content = $content;

        return $this;
    }
}
