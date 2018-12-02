<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Enum;

/**
 * The DList class.
 *
 * @since  2.1
 */
class DList extends AbstractHtmlList
{
    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'dl';

    /**
     * addDescription
     *
     * @param string $title
     * @param string $description
     * @param array  $titleAttribs
     * @param array  $descAttribs
     *
     * @return  static
     */
    public function addDescription($title, $description, $titleAttribs = [], $descAttribs = [])
    {
        $this->addTitle($title, $titleAttribs)
            ->addDesc($description, $descAttribs);

        return $this;
    }

    /**
     * addItem
     *
     * @param   DListDescription|string $item
     * @param   array                   $attribs
     *
     * @return  static
     */
    public function addDesc($item, $attribs = [])
    {
        if (!$item instanceof DListDescription) {
            $item = new DListDescription($item, $attribs);
        }

        $this->content[] = $item;

        return $this;
    }

    /**
     * desc
     *
     * @param   DListDescription|string $item
     * @param   array                   $attribs
     *
     * @return  static
     */
    public function desc($item, $attribs = [])
    {
        return $this->addDesc($item, $attribs);
    }

    /**
     * addItem
     *
     * @param   DListTitle|string $item
     * @param   array             $attribs
     *
     * @return  static
     */
    public function addTitle($item, $attribs = [])
    {
        if (!$item instanceof DListTitle) {
            $item = new DListTitle($item, $attribs);
        }

        $this->content[] = $item;

        return $this;
    }

    /**
     * title
     *
     * @param   DListTitle|string $item
     * @param   array             $attribs
     *
     * @return  static
     */
    public function title($item, $attribs = [])
    {
        return $this->addTitle($item, $attribs);
    }

    /**
     * addItem
     *
     * @param   DListTitle|string $item
     * @param   array             $attribs
     *
     * @return  static
     */
    public function addItem($item, $attribs = [])
    {
        if ($item instanceof DListTitle) {
            $this->addTitle($item);
        } else {
            $this->addDesc($item, $attribs);
        }

        return $this;
    }
}
