<?php

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\DOM\HTMLElement;

/**
 * The CheckboxField class.
 *
 * @method self checkedValue(mixed $value)
 * @method string getCheckedValue()
 *
 * @since  2.0
 */
class CheckboxField extends AbstractInputField
{
    protected string $inputType = 'checkbox';

    /**
     * @inheritDoc
     */
    public function prepareInput(HTMLElement $input): HTMLElement
    {
        $input['type'] = $this->getInputType();
        $input['checked'] = $this->getValue() ? 'checked' : null;
        $input['value'] = $this->getCheckedValue() ?? 'on';

        return $input;
    }

    /**
     * getAccessors
     *
     * @return  array
     *
     * @since   3.1.2
     */
    protected function getAccessors(): array
    {
        return array_merge(
            parent::getAccessors(),
            [
                'checkedValue',
            ]
        );
    }
}
