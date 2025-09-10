<?php

declare(strict_types=1);

namespace Windwalker\Form\Attributes;

use Attribute;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\Form\Form;

/**
 * The Namespace group class.
 */
#[Attribute]
class NS implements AttributeInterface
{
    public string $name;

    /**
     * Fieldset constructor.
     *
     * @param  string  $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(AttributeHandler $handler): callable
    {
        return function () use ($handler) {
            $resolver = $handler->resolver;

            /** @var Form $form */
            $form = $resolver->getOption('form');

            $form->ns($this->name, $handler);

            return $handler->get();
        };
    }
}
