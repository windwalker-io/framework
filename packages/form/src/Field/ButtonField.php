<?php

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\DOM\HTML5Factory;
use Windwalker\DOM\HTMLElement;

use function Windwalker\DOM\h;

/**
 * The ButtonField class.
 *
 * @method  $this  text(string $value = null)
 * @method  mixed  getText()
 * @method  $this  buttonType(string $value)
 * @method  mixed  getButtonType()
 * @method  $this  element(string $value = null)
 * @method  mixed  getElement()
 * @method  $this  href(string $value = null)
 * @method  mixed  getHref()
 * @method  $this  target(string $value = null)
 * @method  mixed  getTarget()
 *
 * @since  2.1.8
 */
class ButtonField extends AbstractField
{
    public const ELEMENT_BUTTON = 'button';

    public const ELEMENT_LINK = 'link';

    public const TYPE_BUTTON = 'button';

    public const TYPE_CLEAR = 'clear';

    public const TYPE_SUBMIT = 'submit';

    /**
     * @inheritDoc
     */
    public function prepareInput(HTMLElement $input): HTMLElement
    {
        $text = $this->getText() ?? $this->getValue();

        $input = h(
            $this->getElement() ?? static::ELEMENT_BUTTON,
            $input->getAttributes(true),
            $text ? HTML5Factory::parse((string) $text, HTML5Factory::TEXT_SPAN) : ''
        );

        $input['type'] = $this->getButtonType() ?? static::ELEMENT_BUTTON;
        $input['id'] = $this->getId();

        return $input;
    }
}
