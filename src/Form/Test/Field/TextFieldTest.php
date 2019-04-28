<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Form\Test\Field;

use Windwalker\Dom\Test\AbstractDomTestCase;
use Windwalker\Form\Field\TextField;

/**
 * Test class of TextField
 *
 * @since 2.0
 */
class TextFieldTest extends AbstractDomTestCase
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
    protected function setUp(): void
    {
        $this->instance = new TextField(
            'flower',
            'Flower',
            [
                'class' => 'stub-flower',
            ]
        );

        $this->instance->setAttribute('id', 'test-field');
        $this->instance->setAttribute('placeholder', 'th');
        $this->instance->setAttribute('size', 60);
        $this->instance->setAttribute('maxlength', 10);
        $this->instance->setAttribute('readonly', true);
        $this->instance->setAttribute('disabled', true);
        $this->instance->setAttribute('onchange', 'javascript:void(0);');
        $this->instance->setAttribute('value', 'sakura');
        $this->instance->setAttribute('attribs', ['data-test-element' => true]);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
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
<input type="text" name="flower" id="test-field" class="stub-flower" placeholder="th" size="60" maxlength="10" readonly="readonly" disabled="disabled" onchange="javascript:void(0);" value="" data-test-element />
HTML;

        $this->assertDomStringEqualsDomString($html, $this->instance->renderInput());
    }
}
