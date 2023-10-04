<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field\Concern;

use Windwalker\DOM\DOMElement;

/**
 * Trait InputOptionsTrait
 */
trait InputOptionsTrait
{
    /**
     * @var callable
     */
    protected $optionWrapperHandler = null;

    /**
     * @var callable
     */
    protected $optionLabelHandler = null;

    /**
     * @var callable
     */
    protected $optionHandler = null;

    public function configureOptionWrapper(DOMElement $wrapper): DOMElement
    {
        if ($handler = $this->getOptionWrapperHandler()) {
            $wrapper = $handler($wrapper, $this) ?? $wrapper;
        }

        return $wrapper;
    }

    public function configureOptionLabel(DOMElement $wrapper): DOMElement
    {
        if ($handler = $this->getOptionLabelHandler()) {
            $wrapper = $handler($wrapper, $this) ?? $wrapper;
        }

        return $wrapper;
    }

    public function configureOption(DOMElement $wrapper): DOMElement
    {
        if ($handler = $this->getOptionHandler()) {
            $wrapper = $handler($wrapper, $this) ?? $wrapper;
        }

        return $wrapper;
    }

    /**
     * @return callable
     */
    public function getOptionWrapperHandler(): ?callable
    {
        return $this->optionWrapperHandler;
    }

    /**
     * @param  callable  $optionWrapperHandler
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptionWrapperHandler(?callable $optionWrapperHandler): static
    {
        $this->optionWrapperHandler = $optionWrapperHandler;

        return $this;
    }

    /**
     * @return callable
     */
    public function getOptionHandler(): ?callable
    {
        return $this->optionHandler;
    }

    /**
     * @param  callable  $optionHandler
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptionHandler(?callable $optionHandler): static
    {
        $this->optionHandler = $optionHandler;

        return $this;
    }

    /**
     * @return callable
     */
    public function getOptionLabelHandler(): ?callable
    {
        return $this->optionLabelHandler;
    }

    /**
     * @param  callable  $optionLabelHandler
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptionLabelHandler(?callable $optionLabelHandler): static
    {
        $this->optionLabelHandler = $optionLabelHandler;

        return $this;
    }
}
