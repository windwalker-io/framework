<?php

declare(strict_types=1);

namespace Windwalker\Form\Field;

use Windwalker\DOM\HTMLElement;

use function Windwalker\DOM\h;

/**
 * The SpacerField class.
 *
 * @method  $this  hr(bool $value = null)
 * @method  mixed  getHr()
 * @method  $this  tag(string $value = null)
 * @method  mixed  getTag()
 *
 * @since  2.0
 */
class SpacerField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function prepareInput(HTMLElement $input): HTMLElement
    {
        if ($this->getHr()) {
            $node = 'hr';

            $content = null;
        } else {
            $node = $this->getTag() ?? 'span';

            $content = $this->getAttribute('description');
        }

        return h($node, $input->getAttributes(true), $content);
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
                'hr' => 'hr',
                'tag' => 'tag',
            ]
        );
    }
}
