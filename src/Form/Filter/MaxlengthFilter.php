<?php
/**
 * Part of phoenix project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Form\Filter;

/**
 * The MaxlengthFilter class.
 *
 * @since  __DEPLOY_VERSION__
 */
class MaxlengthFilter implements FilterInterface
{
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
