<?php

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\DOM\HTMLElement;
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

    public function buildFieldElement(HTMLElement $input, array $options = []): string|HTMLElement
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

    public function option(
        \DOMNode|string|null $text = null,
        ?string $value = null,
        array $attrs = [],
        ?string $group = null
    ): static {
        if ($value === null && is_string($text)) {
            $value = $text;
        }

        $this->addOption(static::createOption($text, $value, $attrs), $group);

        return $this;
    }

    protected function appendOption(HTMLElement $select, HTMLElement|array $option, ?string $group = null): void
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
    public function prepareInput(HTMLElement $input): HTMLElement
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
