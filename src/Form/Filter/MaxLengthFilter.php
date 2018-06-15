<?php
/**
 * Part of phoenix project.
 *
 * @copyright  Copyright (C) 2018 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form\Filter;

/**
 * The MaxlengthFilter class.
 *
 * @since  3.4
 */
class MaxLengthFilter implements FilterInterface
{
    const TEXT_MAX_UTF8 = 21844;
    const TEXT_MAX_ASCII = 65535;

    const LONGTEXT_MAX_UTF8 = 1431655765;
    const LONGTEXT_MAX_ASCII = 4294967295;

    /**
     * Property max.
     *
     * @var int
     */
    protected $max;

    /**
     * Property utf8.
     *
     * @var  bool
     */
    protected $utf8;

    /**
     * MaxlengthFilter constructor.
     *
     * @param $max
     * @param $utf8
     */
    public function __construct($max, $utf8 = true)
    {
        $this->max  = (int) $max;
        $this->utf8 = (bool) $utf8;
    }

    /**
     * clean
     *
     * @param string $text
     *
     * @return  string
     */
    public function clean($text)
    {
        $len = $this->utf8 ? mb_strlen($text) : strlen($text);

        if ($len <= $this->max) {
            return $text;
        }

        return $this->utf8 ? mb_substr($text, 0, $this->max) : substr($text, 0, $this->max);
    }
}
