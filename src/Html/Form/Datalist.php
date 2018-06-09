<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Html\Form;

use Windwalker\Dom\HtmlElement;
use Windwalker\Html\Option;

/**
 * The Datalist class.
 *
 * @since  __DEPLOY_VERSION__
 */
class Datalist extends HtmlElement
{
    /**
     * Element content.
     *
     * @var  Option[]
     */
    protected $content;

    /**
     * Constructor
     *
     * @param string     $id
     * @param mixed|null $options
     * @param array      $attribs
     */
    public function __construct($id, $options = [], $attribs = [])
    {
        $attribs['id'] = $id;

        parent::__construct('datalist', (array) $options, $attribs);
    }

    /**
     * addOption
     *
     * @param Option $option
     *
     * @return  static
     */
    public function addOption(Option $option)
    {
        $this->content[] = $option;

        return $this;
    }

    /**
     * option
     *
     * @param string $value
     * @param array  $attribs
     *
     * @return  static
     */
    public function option($value = null, $attribs = [])
    {
        return $this->addOption(new Option(null, $value, $attribs));
    }

    /**
     * prepareOptions
     *
     * @return  void
     */
    protected function prepareOptions()
    {
        //
    }

    /**
     * __clone
     *
     * @return  void
     */
    public function __clone()
    {
        $this->content = clone $this->content;
    }
}
