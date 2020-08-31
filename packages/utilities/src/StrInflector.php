<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use DomainException;

/**
 * The StrInflector class.
 *
 * @since  __DEPLOY_VERSION__
 */
class StrInflector
{
    protected static ?Inflector $inflector = null;

    /**
     * Checks if a word is in a plural form.
     *
     * @param  string  $word  The string input.
     *
     * @return  boolean  True if word is plural, false if not.
     *
     * @since  2.0
     */
    public static function isPlural(string $word): bool
    {
        // Compute the inflection to cache the values, and compare.
        return static::toPlural(static::toSingular($word)) === $word;
    }

    /**
     * Checks if a word is in a singular form.
     *
     * @param  string  $word  The string input.
     *
     * @return  boolean  True if word is singular, false if not.
     *
     * @since  2.0
     */
    public static function isSingular(string $word): bool
    {
        // Compute the inflection to cache the values, and compare.
        return static::toSingular($word) === $word;
    }

    /**
     * Converts a word into its plural form.
     *
     * @param  string  $word  The singular word to pluralise.
     *
     * @return  string  An inflected string, or false if no rule could be applied.
     *
     * @since  2.0
     */
    public static function toPlural(string $word): string
    {
        static::checkDependency();

        return static::getInflector()->pluralize($word);
    }

    /**
     * Converts a word into its singular form.
     *
     * @param  string  $word  The plural word to singularise.
     *
     * @return  string  An inflected string, or false if no rule could be applied.
     *
     * @since  2.0
     */
    public static function toSingular(string $word): string
    {
        static::checkDependency();

        return static::getInflector()->singularize($word);
    }

    protected static function checkDependency(): void
    {
        if (!class_exists(Inflector::class)) {
            throw new DomainException('Please install doctrine/inflector first');
        }
    }

    protected static function getInflector(): Inflector
    {
        if (!static::$inflector) {
            static::$inflector = InflectorFactory::create()->build();
        }

        return static::$inflector;
    }
}
