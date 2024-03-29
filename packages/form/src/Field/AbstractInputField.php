<?php

declare(strict_types=1);

namespace Windwalker\Form\Field;

/**
 * The AbstractBaseField class.
 */
abstract class AbstractInputField extends AbstractField
{
    protected string $inputType = '';

    /**
     * @return string
     */
    public function getInputType(): string
    {
        return $this->inputType;
    }
}
