<?php

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\DOM\HTMLElement;
use Windwalker\Form\Contract\InputOptionsInterface;
use Windwalker\Form\Field\Concern\InputOptionsTrait;
use Windwalker\Form\FormNormalizer;

use function Windwalker\DOM\h;

/**
 * The RadioField class.
 *
 * @since  2.0
 */
class RadioField extends ListField implements InputOptionsInterface
{
    use InputOptionsTrait;

    public function buildFieldElement(HTMLElement $input, array $options = []): string|HTMLElement
    {
        $attrs = $input->getAttributes(true);
        unset($attrs['name']);

        $input = h('div', $attrs);

        foreach ($this->getOptions() as $option) {
            $option = clone $option;
            $option['type'] = 'radio';
            $option['name'] = $this->getInputName();
            $option['id'] = $this->getId('-' . FormNormalizer::clearAttribute($option['value']));
            $option['data-radio-item-input'] = true;

            if ((string) $option['value'] === (string) $this->getValue()) {
                $option['checked'] = 'checked';
            }

            FormNormalizer::sortAttributes($option, ['id', 'value', 'name']);

            $input->appendChild(
                $this->configureOptionWrapper(
                    h(
                        'div',
                        [
                            'id' => $option['id'] . '-item',
                            'class' => 'radio',
                            'data-radio-item-wrapper' => true,
                        ],
                        [
                            $this->configureOption(h('input', $option->getAttributes(true))),
                            $this->configureOptionLabel(
                                h(
                                    'label',
                                    [
                                        'for' => $option['id'],
                                        'id' => $option['id'] . '-label',
                                        'data-radio-item-label' => true,
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
}
