<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\DOM\DOMElement;
use Windwalker\Filter\Rule\Length;
use Windwalker\Form\Field\Concern\ListOptionsTrait;

use Windwalker\Utilities\TypeCast;

use function Windwalker\DOM\h;

/**
 * The TextField class.
 *
 * @method  $this  placeholder(string $value = null)
 * @method  mixed  getPlaceholder()
 * @method  $this  size(string $value = null)
 * @method  mixed  getSize()
 * @method  $this  autofocus(string $value = null)
 * @method  mixed  getAutofocus()
 * @method  $this  autocomplete(string $value = null)
 * @method  mixed  getAutocomplete()
 * @method  $this  pattern(string $value)
 * @method  mixed  getPattern()
 *
 * @since  2.0
 */
class TextField extends AbstractInputField
{
    use ListOptionsTrait;

    protected string $inputType = 'text';

    public function buildFieldElement(DOMElement $input, array $options = []): string|DOMElement
    {
        $html = parent::buildFieldElement($input, $options);

        if (count($this->options)) {
            $html = h(
                'div',
                [],
                [
                    $html,
                    $this->prepareListElement(
                        h(
                            'datalist',
                            ['id' => $this->buildListId()],
                        )
                    ),
                ]
            );
        }

        return $html;
    }

    protected function appendOption(DOMElement $select, DOMElement|array $option, ?string $group = null): void
    {
        if (is_array($option)) {
            $select->appendChild($optGroup = h('optgroup', ['label' => $group]));

            foreach ($option as $opt) {
                $this->appendOption($optGroup, $opt);
            }

            return;
        }

        $option = clone $option;

        $select->appendChild($option);
    }

    /**
     * @inheritDoc
     */
    public function prepareInput(DOMElement $input): DOMElement
    {
        $input['type'] = $this->getInputType();
        $input['value'] = $this->getValue();
        $input['list'] = $this->getAttribute('list') ?? $this->buildListId();

        return $input;
    }

    public function buildListId(): ?string
    {
        return count($this->options) ? $this->getId() . '-list' : null;
    }

    /**
     * max
     *
     * @param ?int   $length
     * @param  bool  $addFilter
     * @param  bool  $utf8
     *
     * @return  static|mixed
     *
     * @since  3.4.2
     */
    public function maxlength(?int $length = null, bool $addFilter = true, bool $utf8 = true): mixed
    {
        if ($addFilter) {
            $this->addFilter(new Length($length, $utf8));
        }

        return $this->setAttribute('maxlength', (string) $length);
    }

    protected function castToValidValue(mixed $value): mixed
    {
        return TypeCast::toString($value);
    }
}
