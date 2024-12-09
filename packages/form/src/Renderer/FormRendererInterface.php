<?php

declare(strict_types=1);

namespace Windwalker\Form\Renderer;

use Windwalker\DOM\HTMLElement;
use Windwalker\Form\Field\AbstractField;

/**
 * The FormRendererInterface class.
 *
 * @since  3.0
 */
interface FormRendererInterface
{
    /**
     * renderField
     *
     * @param  AbstractField  $field
     * @param  HTMLElement    $wrapper
     * @param  array          $options
     *
     * @return string
     */
    public function renderField(AbstractField $field, HTMLElement $wrapper, array $options = []): string;

    /**
     * renderLabel
     *
     * @param  AbstractField  $field
     * @param  HTMLElement    $label
     * @param  array          $options
     *
     * @return string
     */
    public function renderLabel(AbstractField $field, HTMLElement $label, array $options = []): string;

    /**
     * renderInput
     *
     * @param  AbstractField  $field
     * @param  HTMLElement    $input
     * @param  array          $options
     *
     * @return string
     */
    public function renderInput(AbstractField $field, HTMLElement $input, array $options = []): string;
}
