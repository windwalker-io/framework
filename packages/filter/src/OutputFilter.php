<?php

declare(strict_types=1);

namespace Windwalker\Filter;

use Windwalker\Filter\Unicode\UnicodeHelper;

/**
 * Windwalker Output Filter
 *
 * @since  2.0
 */
class OutputFilter
{
    public const KEEP_IMAGES = 1 << 0;

    public const KEEP_IFRAMES = 1 << 1;

    public const KEEP_SCRIPTS = 1 << 2;

    public const KEEP_STYLES = 1 << 3;

    public const KEEP_LINKS = 1 << 4;

    /**
     * This method processes a string and replaces all instances of & with &amp; in links only.
     *
     * @param  string  $input  String to process
     *
     * @return  string  Processed string
     */
    public static function linkXHTMLSafe(string $input): string
    {
        $regex = 'href="([^"]*(&(amp;){0})[^"]*)*?"';

        return preg_replace_callback(
            "#$regex#i",
            function ($m) {
                $rx = '&(?!amp;)';

                return preg_replace('#' . $rx . '#', '&amp;', $m[0]);
            },
            $input
        );
    }

    /**
     * This method processes a string and replaces all accented UTF-8 characters by unaccented
     * ASCII-7 "equivalents", whitespaces are replaced by hyphens and the string is lowercase.
     *
     * @param  string  $string  String to process
     *
     * @return  string  Processed string
     *
     * @since   2.0
     */
    public static function stringURLSafe(string $string): string
    {
        // Remove any '-' from the string since they will be used as concatenaters
        $str = str_replace('-', ' ', $string);

        $str = UnicodeHelper::latinToAscii($str);

        // Trim white spaces at beginning and end of alias and make lowercase
        $str = trim(UnicodeHelper::strtolower($str));

        // Remove any duplicate whitespace, and ensure all characters are alphanumeric
        $str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $str);

        // Trim dashes at beginning and end of alias
        $str = trim($str, '-');

        return $str;
    }

    /**
     * This method implements unicode slugs instead of transliteration.
     *
     * @param  string  $string  String to process
     *
     * @return  string  Processed string
     *
     * @since   2.0
     */
    public static function stringURLUnicodeSlug(string $string): string
    {
        // Replace double byte whitespaces by single byte (East Asian languages)
        $str = preg_replace('/\xE3\x80\x80/', ' ', $string);

        // Remove any '-' from the string as they will be used as concatenator.
        // Would be great to let the spaces in but only Firefox is friendly with this

        $str = str_replace('-', ' ', $str);

        // Replace forbidden characters by whitespaces
        $str = preg_replace('#[:\#\*"@+=;!><&\.%()\]\/\'\\\\|\[]#', "\x20", $str);

        // Delete all '?'
        $str = str_replace('?', '', $str);

        // Trim white spaces at beginning and end of alias and make lowercase
        $str = trim(UnicodeHelper::strtolower($str));

        // Remove any duplicate whitespace and replace whitespaces by hyphens
        $str = preg_replace('#\x20+#', '-', $str);

        return $str;
    }

    /**
     * Replaces &amp; with & for XHTML compliance
     *
     * @param  string  $text  Text to process
     *
     * @return  string  Processed string.
     */
    public static function ampReplace(string $text): string
    {
        $text = str_replace('&&', '*--*', $text);
        $text = str_replace('&#', '*-*', $text);
        $text = str_replace('&amp;', '&', $text);
        $text = preg_replace('|&(?![\w]+;)|', '&amp;', $text);
        $text = str_replace('*-*', '&#', $text);
        $text = str_replace('*--*', '&&', $text);

        return $text;
    }

    /**
     * Cleans text of all formatting and scripting code
     *
     * @param  string  $text  Text to clean
     *
     * @return  string  Cleaned text.
     */
    public static function cleanText(string $text): string
    {
        $text = preg_replace("'<script[^>]*>.*?</script>'si", '', $text);
        $text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2 (\1)', $text);
        $text = preg_replace('/<!--.+?-->/', '', $text);
        $text = preg_replace('/{.+?}/', '', $text);
        $text = preg_replace('/&nbsp;/', ' ', $text);
        $text = preg_replace('/&amp;/', ' ', $text);
        $text = preg_replace('/&quot;/', ' ', $text);
        $text = strip_tags($text);
        $text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');

        return $text;
    }

    /**
     * Strip img-tags from string
     *
     * @param  string  $string  Sting to be cleaned.
     *
     * @return  string  Cleaned string
     */
    public static function stripImages(string $string): string
    {
        return preg_replace('#(<[/]?img.*>)#U', '', $string);
    }

    /**
     * Strip iframe-tags from string
     *
     * @param  string  $string  Sting to be cleaned.
     *
     * @return  string  Cleaned string
     */
    public static function stripIframes(string $string): string
    {
        return preg_replace('#(<[/]?iframe.*>)#U', '', $string);
    }

    /**
     * stripScript
     *
     * @param  string  $string
     *
     * @return  string
     */
    public static function stripScript(string $string): string
    {
        return (string) preg_replace("'<script[^>]*>.*?</script>'si", '', $string);
    }

    /**
     * stripStyle
     *
     * @param  string  $string
     *
     * @return  mixed
     */
    public static function stripStyle(string $string): string
    {
        return (string) preg_replace("'<style[^>]*>.*?</style>'si", '', $string);
    }

    /**
     * stripLinks
     *
     * @param  string  $string
     *
     * @return  mixed
     */
    public static function stripLinks(string $string): string
    {
        return (string) preg_replace('/<link[^>]*>/', '', $string);
    }

    public static function safeHTML(string $html, int $flags = 0): string
    {
        if (!($flags & static::KEEP_IMAGES)) {
            $html = static::stripImages($html);
        }

        if (!($flags & static::KEEP_IFRAMES)) {
            $html = static::stripIframes($html);
        }

        if (!($flags & static::KEEP_SCRIPTS)) {
            $html = static::stripScript($html);
        }

        if (!($flags & static::KEEP_STYLES)) {
            $html = static::stripStyle($html);
        }

        if (!($flags & static::KEEP_LINKS)) {
            $html = static::stripLinks($html);
        }

        return $html;
    }
}
