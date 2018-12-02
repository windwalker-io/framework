<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Data;

/**
 * The DataSet Interface
 *
 * @sine 1.0
 */
interface DataSetInterface
{
    /**
     * Bind an array contains multiple data into this object.
     *
     * @param   array $dataset The data array or object.
     *
     * @return  Data Return self to support chaining.
     */
    public function bind($dataset);

    /**
     * Is this data set empty?
     *
     * @return  boolean True if empty.
     */
    public function isNull();

    /**
     * Is this data set has properties?
     *
     * @return  boolean True is exists.
     */
    public function notNull();

    /**
     * Dump all data as array.
     *
     * @param bool $recursive
     *
     * @return Data[]
     */
    public function dump($recursive = false);
}
