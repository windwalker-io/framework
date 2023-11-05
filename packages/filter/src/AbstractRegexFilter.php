<?php

declare(strict_types=1);

namespace Windwalker\Filter;

/**
 * The AbstractRegex class.
 */
abstract class AbstractRegexFilter extends AbstractFilter
{
    public const TYPE_MATCH = 'match';

    public const TYPE_REPLACE = 'replace';

    protected string $type = self::TYPE_REPLACE;

    /**
     * @inheritDoc
     */
    public function filter(mixed $value): mixed
    {
        $type = $this->type;

        return $this->$type((string) $value);
    }

    /**
     * Method to get property Regex
     *
     * @return  string
     */
    abstract public function getRegex(): string;

    public function match(string $value): string
    {
        preg_match_all($this->getRegex(), $value, $matches);

        return implode('', $matches[0] ?? []) ?? '';
    }

    /**
     * replace
     *
     * @param  string  $value
     *
     * @return string|null
     */
    public function replace(string $value): string
    {
        return preg_replace($this->getRegex(), '', $value) ?? '';
    }
}
