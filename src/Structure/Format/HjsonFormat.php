<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    __LICENSE__
 */

namespace Windwalker\Structure\Format;

use HJSON\HJSONParser;
use HJSON\HJSONStringifier;

/**
 * The HjsonFormat class.
 *
 * @since  __DEPLOY_VERSION__
 */
class HjsonFormat implements FormatInterface
{
    /**
     * Converts an object into a formatted string.
     *
     * @param object $struct  Data Source Object.
     * @param array  $options An array of options for the formatter.
     *
     * @return  string  Formatted string.
     *
     * @since   2.0
     */
    public static function structToString($struct, array $options = [])
    {
        return (new HJSONStringifier())->stringify($struct, $options);
    }

    /**
     * Converts a formatted string into an object.
     *
     * @param string $data    Formatted string
     * @param array  $options An array of options for the formatter.
     *
     * @return  object  Data Object
     *
     * @since   2.0
     */
    public static function stringToStruct($data, array $options = [])
    {
        return (new HJSONParser())->parse($data, $options);
    }
}