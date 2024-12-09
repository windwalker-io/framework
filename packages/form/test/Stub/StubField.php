<?php

declare(strict_types=1);

namespace Windwalker\Form\Test\Stub;

use Windwalker\DOM\HTMLElement;
use Windwalker\Form\Field\AbstractField;

/**
 * The StubField class.
 *
 * @since  2.0
 */
class StubField extends AbstractField
{
    /**
     * Property type.
     *
     * @var  string
     */
    protected string $inputType = 'stub';

    public function prepareInput(HTMLElement $input): HTMLElement
    {
        $input['type'] = 'text';
        $input['id'] = $this->getId();
        $input['name'] = $this->getInputName();
        $input['value'] = $this->getValue();

        return $input;
    }
}
