<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form\Test\Field;

use Windwalker\Dom\Test\AbstractDomTestCase;
use Windwalker\Form\Field\ButtonField;
use Windwalker\Form\Field\TextField;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class ButtonFieldTest extends AbstractDomTestCase
{
    /**
     * Test instance.
     *
     * @var TextField
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->instance = new ButtonField(
            'flower',
            'Flower',
            [
                'class' => 'stub-flower',
            ]
        );

        $this->instance->setAttribute('id', 'test-field');
        $this->instance->setAttribute('readonly', true);
        $this->instance->setAttribute('disabled', true);
        $this->instance->setAttribute('attribs', ['data-test-element' => true]);
        $this->instance->setValue('Sakura');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
    }

    /**
     * Method to test prepareAttributes().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\TextField::prepareAttributes
     */
    public function testRender()
    {
        $html = <<<HTML
<button type="submit" name="flower" id="test-field" class="stub-flower" disabled="disabled" data-test-element>Sakura</button>
HTML;

        $this->assertHtmlFormatEquals($html, $this->instance->renderInput());
    }
}
