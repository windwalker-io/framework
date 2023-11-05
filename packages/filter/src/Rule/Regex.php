<?php

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractRegexFilter;

/**
 * The Regex class.
 */
class Regex extends AbstractRegexFilter
{
    /**
     * The regular expression to use in testing value.
     *
     * @var string|null
     */
    protected ?string $regex = null;

    /**
     * Class init.
     *
     * @param  string|null  $regex
     * @param  string       $type
     */
    public function __construct(?string $regex = null, string $type = self::TYPE_MATCH)
    {
        $this->regex = $regex;
        $this->type = $type;
    }

    /**
     * Method to get property Regex
     *
     * @return  string
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * Method to set property regex
     *
     * @param  string  $regex
     *
     * @return  static  Return self to support chaining.
     */
    public function setRegex(string $regex): static
    {
        $this->regex = $regex;

        return $this;
    }
}
