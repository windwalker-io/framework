<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Html\Helper;

use Windwalker\Test\Helper\TestDomHelper;

/**
 * The HtmlHelper class.
 *
 * @since  2.0
 */
abstract class HtmlHelper extends TestDomHelper
{
    /**
     * Repair HTML. If Tidy not exists, use repair function.
     *
     * @param   string  $html     The HTML string to repair.
     * @param   boolean $use_tidy Force tidy or not.
     *
     * @return  string  Repaired HTML.
     */
    public static function repair($html, $use_tidy = true)
    {
        if (function_exists('tidy_repair_string') && $use_tidy) {
            $TidyConfig = [
                'indent' => true,
                'output-xhtml' => true,
                'show-body-only' => true,
                'wrap' => false,
            ];

            return tidy_repair_string($html, $TidyConfig, 'utf8');
        } else {
            $arr_single_tags = ['meta', 'img', 'br', 'link', 'area'];

            // Put all opened tags into an array
            preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $html, $result);
            $openedtags = $result[1];

            // Put all closed tags into an array
            preg_match_all("#</([a-z]+)>#iU", $html, $result);

            $closedtags = $result[1];
            $len_opened = count($openedtags);

            // All tags are closed
            if (count($closedtags) == $len_opened) {
                return $html;
            }

            $openedtags = array_reverse($openedtags);

            // Close tags
            for ($i = 0; $i < $len_opened; $i++) {
                if (!in_array($openedtags[$i], $closedtags)) {
                    if (!in_array($openedtags[$i], $arr_single_tags)) {
                        $html .= "</" . $openedtags[$i] . ">";
                    }
                } else {
                    unset($closedtags[array_search($openedtags[$i], $closedtags)]);
                }
            }

            return $html;
        }
    }

    /**
     * Method to get a JavaScript object notation string from an array
     *
     * @param mixed $data     The data to convert to JavaScript object notation
     * @param bool  $quoteKey Quote json key or not.
     *
     * @return string JavaScript object notation representation of the array
     *
     * @since  2.0
     */
    public static function getJSObject($data = [], $quoteKey = false)
    {
        if ($data === null) {
            return 'null';
        };

        $output = '';

        switch (gettype($data)) {
            case 'boolean':
                $output .= $data ? 'true' : 'false';
                break;

            case 'float':
            case 'double':
            case 'integer':
                $output .= $data + 0;
                break;

            case 'array':
                if (!static::isAssociativeArray($data)) {
                    $child = [];

                    foreach ($data as $value) {
                        $child[] = static::getJSObject($value, $quoteKey);
                    }

                    $output .= '[' . implode(',', $child) . ']';
                    break;
                }
                // Go to next body

            case 'object':
                $array = is_object($data) ? get_object_vars($data) : $data;

                $row = [];

                foreach ($array as $key => $value) {
                    $key = json_encode($key);

                    if (!$quoteKey) {
                        $key = substr(substr($key, 0, -1), 1);
                    }

                    $row[] = $key . ':' . static::getJSObject($value, $quoteKey);
                }

                $output .= '{' . implode(',', $row) . '}';
                break;

            default:  // anything else is treated as a string
                return strpos($data, '\\') === 0 ? substr($data, 1) : json_encode($data);
                break;
        }

        return $output;
    }

    /**
     * Method to determine if an array is an associative array.
     *
     * @param   array $array An array to test.
     *
     * @return  boolean  True if the array is an associative array.
     *
     * @since   3.0
     */
    public static function isAssociativeArray($array)
    {
        if (is_array($array)) {
            foreach (array_keys($array) as $k => $v) {
                if ($k !== $v) {
                    return true;
                }
            }
        }

        return false;
    }
}
