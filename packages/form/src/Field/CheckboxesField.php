<?php

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\Data\Collection;
use Windwalker\DOM\DOMElement;
use Windwalker\Form\Contract\InputOptionsInterface;
use Windwalker\Form\Field\Concern\InputOptionsTrait;
use Windwalker\Form\FormNormalizer;

use function Windwalker\DOM\h;

/**
 * The CheckboxesField class.
 *
 * @since  2.0
 */
class CheckboxesField extends ListField implements InputOptionsInterface
{
    use InputOptionsTrait;

    public function buildFieldElement(DOMElement $input, array $options = []): string|DOMElement
    {
        $attrs = $input->getAttributes(true);
        unset($attrs['name']);

        $input = h('div', $attrs);

        foreach ($this->getOptions() as $option) {
            $option = clone $option;
            $option['type'] = 'checkbox';
            $option['name'] = $this->getInputName('[]');
            $option['id'] = $this->getId('-' . FormNormalizer::clearAttribute($option['value']));
            $option['disabled'] = $this->isDisabled();
            $option['readonly'] = $this->isReadonly();
            $option['data-checkbox-item-input'] = true;

            if (in_array($option['value'], $this->getValue())) {
                $option['checked'] = 'checked';
            }

            FormNormalizer::sortAttributes($option, ['id', 'value', 'name']);

            $input->appendChild(
                $this->configureOptionWrapper(
                    h(
                        'div',
                        [
                            'id' => $option['id'] . '-item',
                            'class' => 'checkbox',
                            'data-checkbox-item-wrapper' => true,
                        ],
                        [
                            $this->configureOption(h('input', $option->getAttributes(true))),
                            $this->configureOptionLabel(
                                h(
                                    'label',
                                    [
                                        'for' => $option['id'],
                                        'id' => $option['id'] . '-label',
                                        'data-checkbox-item-label' => true,
                                    ],
                                    $option->childNodes
                                )
                            ),
                        ]
                    )
                )
            );
        }

        return $input;
    }

    public function isMultiple(): bool
    {
        return true;
    }
}
