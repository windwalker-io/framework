<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\IO;

/**
 * Windwalker Input Files Class
 *
 * @since  2.0
 */
class FilesInput extends Input
{
    /**
     * The pivoted data from a $_FILES or compatible array.
     *
     * @var    array
     * @since  2.0
     */
    protected $decodedData = [];

    /**
     * Prepare source.
     *
     * @param   array   $source    Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
     * @param   boolean $reference If set to true, he source in first argument will be reference.
     *
     * @return  void
     */
    public function prepareSource(&$source = null, $reference = false)
    {
        $this->data = &$_FILES;
    }

    /**
     * Gets a value from the input data.
     *
     * @param   string $name      The name of the input property (usually the name of the files INPUT tag) to get.
     * @param   mixed  $default   The default value to return if the named property does not exist.
     * @param   string $filter    The filter to apply to the value.
     * @param   string $separator The symbol to separate path.
     *
     * @return mixed The filtered input value.
     *
     * @see     JFilterInput::clean
     * @since   2.0
     */
    public function get($name, $default = null, $filter = 'raw', $separator = '.')
    {
        if (strpos($name, $separator)) {
            return parent::get($name, $default, $filter, $separator);
        }

        if (isset($this->data[$name])) {
            $results = $this->decodeData(
                [
                    $this->data[$name]['name'],
                    $this->data[$name]['type'],
                    $this->data[$name]['tmp_name'],
                    $this->data[$name]['error'],
                    $this->data[$name]['size'],
                ]
            );

            return $results;
        }

        return $default;
    }

    /**
     * Method to decode a data array.
     *
     * @param   array $data The data array to decode.
     *
     * @return  array
     *
     * @since   2.0
     */
    protected function decodeData(array $data)
    {
        $result = [];

        if (is_array($data[0])) {
            foreach ($data[0] as $k => $v) {
                $result[$k] = $this->decodeData([$data[0][$k], $data[1][$k], $data[2][$k], $data[3][$k], $data[4][$k]]);
            }

            return $result;
        }

        return [
            'name' => $data[0],
            'type' => $data[1],
            'tmp_name' => $data[2],
            'error' => $data[3],
            'size' => $data[4],
        ];
    }

    /**
     * Sets a value
     *
     * @param   string $name      Name of the value to set.
     * @param   mixed  $value     Value to assign to the input.
     * @param   string $separator Symbol to separate path.
     *
     * @since   2.0
     */
    public function set($name, $value, $separator = '.')
    {
    }
}
