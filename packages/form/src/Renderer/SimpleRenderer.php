<?php

declare(strict_types=1);

namespace Windwalker\Form\Renderer;

use Windwalker\DOM\HTMLElement;
use Windwalker\Form\Field\AbstractField;

/**
 * The SimpleRenderer class.
 */
class SimpleRenderer implements FormRendererInterface
{
    /**
     * @inheritDoc
     */
    public function renderField(AbstractField $field, HTMLElement $wrapper, array $options = []): string
    {
        return (string) $field->buildWrapper($wrapper, $options);
    }

    /**
     * @inheritDoc
     */
    public function renderLabel(AbstractField $field, HTMLElement $label, array $options = []): string
    {
        return (string) $field->buildLabel($label, $options);
    }

    /**
     * @inheritDoc
     */
    public function renderInput(AbstractField $field, HTMLElement $input, array $options = []): string
    {
        return (string) $field->buildFieldElement($input, $options);
    }
}
