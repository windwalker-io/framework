<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Dom\Builder;

/**
 * HTML Builder helper.
 *
 * @since 2.0
 */
class HtmlBuilder extends DomBuilder
{
    /**
     * Unpaired elements.
     *
     * @var  array
     */
    protected static $unpairedElements = [
        'img',
        'br',
        'hr',
        'area',
        'param',
        'wbr',
        'base',
        'link',
        'meta',
        'input',
        'option',
        'a',
        'source',
    ];

    /**
     * Property trueValueMapping.
     *
     * @var  array
     */
    protected static $trueValueMapping = [
        'readonly' => 'readonly',
        'disabled' => 'disabled',
        'multiple' => 'true',
        'checked' => 'checked',
        'selected' => 'selected',
    ];

    /**
     * Create a html element.
     *
     * @param string $name      Element tag name.
     * @param mixed  $content   Element content.
     * @param array  $attribs   Element attributes.
     * @param bool   $forcePair Force pair it.
     *
     * @return  string Created element string.
     */
    public static function create($name, $content = '', $attribs = [], $forcePair = false)
    {
        $paired = $forcePair ?: !in_array(strtolower($name), static::$unpairedElements);

        return parent::create($name, $content, $attribs, $paired);
    }

    /**
     * buildAttributes
     *
     * @param array $attribs
     *
     * @return  string
     */
    public static function buildAttributes($attribs)
    {
        $attribs = static::mapAttrValues($attribs);

        return parent::buildAttributes($attribs);
    }

    /**
     * mapAttrValues
     *
     * @param array $attribs
     *
     * @return  mixed
     */
    protected static function mapAttrValues($attribs)
    {
        foreach (static::$trueValueMapping as $key => $value) {
            $attribs[$key] = !empty($attribs[$key]) ? $value : null;
        }

        return $attribs;
    }
}
