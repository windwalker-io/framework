<?php declare(strict_types=1);
/**
 * Part of Windwalker project.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Form\Test\Mock;

use Windwalker\Dom\HtmlElement;
use Windwalker\Form\Field\AbstractField;
use Windwalker\Form\Renderer\FormRendererInterface;

/**
 * The MockFormRenderer class.
 *
 * @since  3.0
 */
class MockFormRenderer implements FormRendererInterface
{
    /**
     * renderField
     *
     * @param AbstractField $field
     * @param array         $attribs
     * @param array         $options
     *
     * @return string
     */
    public function renderField(AbstractField $field, array $attribs = [], array $options = [])
    {
        return (string) new HtmlElement(
            'mock', [
            $field->renderLabel(),
            $field->renderInput(),
        ], $attribs
        );
    }

    /**
     * renderLabel
     *
     * @param AbstractField $field
     * @param array         $attribs
     *
     * @param array         $options
     *
     * @return string
     */
    public function renderLabel(AbstractField $field, array $attribs = [], array $options = [])
    {
        return 'Hello ';
    }

    /**
     * renderInput
     *
     * @param AbstractField $field
     * @param array         $attribs
     *
     * @param array         $options
     *
     * @return string
     */
    public function renderInput(AbstractField $field, array $attribs = [], array $options = [])
    {
        return 'World: ' . $field->getFieldName();
    }
}
