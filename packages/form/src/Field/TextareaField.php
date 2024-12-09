<?php

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\DOM\HTMLElement;

use function Windwalker\DOM\h;

/**
 * The TextareaField class.
 *
 * @method  $this  cols(string|int $value = null)
 * @method  mixed  getCols()
 * @method  $this  rows(string|int $value = null)
 * @method  mixed  getRows()
 *
 * @since  2.0
 */
class TextareaField extends TextField
{
    /**
     * @inheritDoc
     */
    public function prepareInput(HTMLElement $input): HTMLElement
    {
        $input = h('textarea', $input->getAttributes(true), $this->getValue());

        $input = parent::prepareInput($input);

        $input->removeAttribute('type');
        $input->removeAttribute('value');

        return $input;
    }
}
