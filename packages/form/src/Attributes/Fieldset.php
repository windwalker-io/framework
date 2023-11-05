<?php

declare(strict_types=1);

namespace Windwalker\Form\Attributes;

use Attribute;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Attributes\AttributeInterface;
use Windwalker\Form\Form;

/**
 * The Fieldset class.
 */
#[Attribute]
class Fieldset implements AttributeInterface
{
    public string $name;

    protected ?string $title;

    protected Form $form;

    /**
     * Fieldset constructor.
     *
     * @param  string       $name
     * @param  string|null  $title
     */
    public function __construct(string $name, ?string $title = '')
    {
        $this->name = $name;
        $this->title = $title;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(AttributeHandler $handler): callable
    {
        return function () use ($handler) {
            $resolver = $handler->getResolver();

            /** @var Form $form */
            $form = $resolver->getOption('form');

            $fieldset = $form->fieldset($this->name, $handler);

            $fieldset->title($this->title);

            return $handler->get();
        };
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     *
     * @return  static  Return self to support chaining.
     */
    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param  string|null  $title
     *
     * @return  static  Return self to support chaining.
     */
    public function title(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function register(callable $handler): static
    {
        $this->getForm()->fieldset($this->getName(), $handler);

        return $this;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @param  Form  $form
     *
     * @return  static  Return self to support chaining.
     */
    public function setForm(Form $form): static
    {
        $this->form = $form;

        return $this;
    }
}
