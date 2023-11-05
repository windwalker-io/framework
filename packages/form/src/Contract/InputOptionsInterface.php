<?php

declare(strict_types=1);

namespace Windwalker\Form\Contract;

/**
 * Interface InputOptionsInterface
 */
interface InputOptionsInterface
{
    /**
     * @param  callable  $optionWrapperHandler
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptionWrapperHandler(?callable $optionWrapperHandler): static;

    /**
     * @param  callable  $optionHandler
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptionHandler(?callable $optionHandler): static;

    /**
     * @param  callable  $optionLabelHandler
     *
     * @return  static  Return self to support chaining.
     */
    public function setOptionLabelHandler(?callable $optionLabelHandler): static;
}
