<?php

declare(strict_types=1);

namespace Windwalker\Form\Test\Field;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Filter\Rule\IPAddress;
use Windwalker\Filter\Rule\Regex;
use Windwalker\Form\Test\Mock\MockFilter;
use Windwalker\Form\Test\Stub\StubField;
use Windwalker\Form\Test\Stub\StubFilter;
use Windwalker\Test\Traits\DOMTestTrait;

/**
 * Test class of AbstractField
 *
 * @since 2.0
 */
class AbstractFieldTest extends TestCase
{
    use DOMTestTrait;

    /**
     * Test instance.
     *
     * @var StubField
     */
    protected StubField $instance;

    #[Before]
    protected function createStubField(): void
    {
        $this->instance = new StubField(
            'flower',
            'Flower',
            [
                'placeholder' => 'The Flower',
                'class' => 'stub-flower',
            ]
        );
    }

    /**
     * Method to test renderInput().
     *
     * @return void
     */
    #[Test]
    public function renderInput(): void
    {
        // phpcs:disable
        $expect = <<<HTML
<input id="input-flower" name="flower" class="stub-flower" data-field-input placeholder="The Flower" type="text">
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());

        // Control
        $this->instance->setNamespace('windwalker');

        $expect = <<<HTML
<input id="input-windwalker-flower" name="windwalker[flower]" class="stub-flower" data-field-input placeholder="The Flower" type="text">
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());

        // Default value
        $this->instance->set('default', 'default-value');

        $expect = <<<HTML
<input id="input-windwalker-flower" name="windwalker[flower]" class="stub-flower" data-field-input placeholder="The Flower" type="text" value="default-value">
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());

        // Value
        $this->instance->setValue('sakura');

        $expect = <<<HTML
<input id="input-windwalker-flower" name="windwalker[flower]" class="stub-flower" data-field-input placeholder="The Flower" type="text" value="sakura">
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());

        // Group
        $this->instance->appendNamespace('foo/bar');

        $expect = <<<HTML
<input id="input-windwalker-foo-bar-flower" name="windwalker[foo][bar][flower]" class="stub-flower" data-field-input placeholder="The Flower" type="text" value="sakura">
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());
        // phpcs:enable
    }

    /**
     * Method to test buildInput().
     *
     * @return void
     */
    #[Test]
    public function buildInput(): void
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test renderLabel().
     *
     * @return void
     */
    #[Test]
    public function renderLabel(): void
    {
        $expect = <<<HTML
<label id="input-flower-label" data-field-label for="input-flower">Flower</label>
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderLabel());
    }

    /**
     * Method to test renderView().
     *
     * @return void
     */
    #[Test]
    public function renderView(): void
    {
        $this->instance->setValue('sakura');

        self::assertEquals('sakura', $this->instance->renderView());
    }

    /**
     * Method to test render().
     *
     * @return void
     */
    #[Test]
    public function renderFieldWithLabelAndInput(): void
    {
        $this->instance->setNamespace('windwalker');
        $this->instance->setValue('sakura');

        $this->instance->addWrapperClass('form-group');
        $this->instance->wrapperAttr('data-toggle', 'foo');
        $this->instance->labelAttr('data-toggle', 'bar');
        $this->instance->setAttribute('data-test-element', true);

        $expect = <<<HTML
<div id="input-windwalker-flower-wrapper" class="form-group" data-field-wrapper data-toggle="foo">
    <label id="input-windwalker-flower-label" data-field-label data-toggle="bar" for="input-windwalker-flower">Flower</label>
    <div>
        <input id="input-windwalker-flower" name="windwalker[flower]" class="stub-flower" data-field-input data-test-element placeholder="The Flower" type="text" value="sakura">
    </div>
</div>
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->render());

        $expect = <<<HTML
<div id="input-windwalker-flower-wrapper" class="form-group" data-field-wrapper data-toggle="foo">
    <div>
        <input id="input-windwalker-flower" name="windwalker[flower]" class="stub-flower" data-field-input data-test-element placeholder="The Flower" type="text" value="sakura">
    </div>
</div>
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->render(['no_label' => true]));
    }

    /**
     * Method to test getLabel().
     *
     * @return void
     */
    #[Test]
    public function getLabelName(): void
    {
        self::assertEquals('Flower', $this->instance->getLabelName());
    }

    /**
     * Method to test getId().
     *
     * @return void
     */
    #[Test]
    public function getIdIncludesNamespaceAndName(): void
    {
        self::assertEquals('input-flower', $this->instance->getId());

        $this->instance->setNamespace('windwalker');

        self::assertEquals('input-windwalker-flower', $this->instance->getId());

        $this->instance->setName('a.b-c_d:e');

        self::assertEquals('input-windwalker-a-b-c_d-e', $this->instance->getId());
    }

    /**
     * Method to test validate().
     *
     * @return void
     */
    #[Test]
    public function validateAppliesRequiredAndRuleChecks(): void
    {
        $field = StubField::create(
            'flower',
            'Flower',
            [
                'placeholder' => 'The Flower',
                'class' => 'stub-flower',
            ],
            null
        )
            ->addValidator(IPAddress::class);

        // No value will not validate
        self::assertTrue($field->validate(null)->isSuccess());

        // Do validate
        self::assertTrue($field->validate('123.21.23.156')->isSuccess());

        self::assertTrue($field->validate('/var/foo/bar')->isFailure());
    }

    /**
     * Method to test checkRequired().
     *
     * @return void
     */
    #[Test]
    public function checkRequired(): void
    {
        $field = new StubField(
            'flower',
            'Flower',
            [
                'placeholder' => 'The Flower',
                'class' => 'stub-flower',
                'required' => true,
            ]
        );

        self::assertTrue($field->validate(null)->isFailure());

        self::assertTrue($field->validate('123')->isSuccess());
    }

    /**
     * Method to test checkRule().
     *
     * @return void
     */
    #[Test]
    public function checkRule(): void
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test filter().
     *
     * @return void
     */
    #[Test]
    public function filterValue(): void
    {
        $field = StubField::create(
            'flower',
            'Flower',
            [
                'placeholder' => 'The Flower',
                'class' => 'stub-flower',
            ]
        )
            ->addFilter('cmd|func(strtoupper)');

        self::assertEquals('ABCFOO_BAR-YOODIVDATADIV456789', $field->filter('abc foo_bar-yoo<div>data</div>456:789'));

        $field->addFilter(new MockFilter())->setValue('foo');

        self::assertEquals('abc', $field->filter('foo'));
    }

    /**
     * Method to test getName().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::getName
     */
    #[Test]
    public function getNameIgnoresControlAndValue(): void
    {
        $this->instance->setControl('windwalker');
        $this->instance->setValue('sakura');

        self::assertEquals('flower', $this->instance->getName());
    }

    /**
     * Method to test setName().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::setName
     */
    #[Test]
    public function setNameUpdatesGeneratedId(): void
    {
        $this->instance->setNamespace('windwalker');
        $this->instance->appendNamespace('goo');
        $this->instance->setName('yoo');

        self::assertEquals('input-windwalker-goo-yoo', $this->instance->getId());
    }

    /**
     * Method to test getFieldName().
     *
     * @return void
     */
    #[Test]
    public function getFullName(): void
    {
        $this->instance->setNamespace('windwalker');

        self::assertEquals('windwalker[flower]', $this->instance->getInputName());

        $this->instance->appendNamespace('foo/bar');
        $this->instance->setName('yoo');

        self::assertEquals('windwalker[foo][bar][yoo]', $this->instance->getInputName());
    }

    /**
     * Method to test setFieldName().
     *
     * @return void
     */
    #[Test]
    public function setFieldName(): void
    {
        $this->instance->setAttribute('name', 'foo[bar]');

        self::assertEquals('foo[bar]', $this->instance->getInputName());
    }

    /**
     * Method to test getGroup().
     *
     * @return void
     */
    #[Test]
    public function getAndSetGroup(): void
    {
        $this->instance->appendNamespace('wind');

        // phpcs:disable
        $expect = <<<HTML
<input id="input-wind-flower" name="wind[flower]" class="stub-flower" data-field-input placeholder="The Flower" type="text">
HTML;

        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());

        $this->instance->setNamespace('wind/walker');

        $expect = <<<HTML
<input id="input-wind-walker-flower" name="wind[walker][flower]" class="stub-flower" data-field-input placeholder="The Flower" type="text">
HTML;

        // phpcs:enable
        self::assertHtmlFormatEquals($expect, $this->instance->renderInput());
    }

    /**
     * Method to test getFieldset().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::getFieldset
     * @TODO   Implement testGetFieldset().
     */
    #[Test]
    public function getFieldset(): void
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setFieldset().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::setFieldset
     * @TODO   Implement testSetFieldset().
     */
    #[Test]
    public function setFieldset(): void
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getValue().
     *
     * @return void
     */
    #[Test]
    public function getAndSetValue(): void
    {
        // Test default value
        $this->instance->defaultValue('joo');

        self::assertEquals('joo', $this->instance->getValue());

        // Test set value
        $this->instance->setValue('Sakura');

        self::assertEquals('Sakura', $this->instance->getValue());
    }

    /**
     * Method to test setFilter().
     *
     * @return void
     */
    #[Test]
    public function getAndSetFilter(): void
    {
        // Set filter type
        $this->instance->resetFilters()->addFilter(new Regex('/[0-9]+/'));

        self::assertEquals('123', $this->instance->filter('123'));

        $this->instance->resetFilters()->addFilter(new Regex('/[^0-9]+/'));

        self::assertEquals('abccba', $this->instance->filter('abc123cba'));

        // Set filter handler
        $closure = static fn($value) => filter_var($value, FILTER_SANITIZE_NUMBER_INT);

        $this->instance->resetFilters()->addFilter($closure);

        self::assertEquals('123123', $this->instance->filter('abc123cba123fgh'));

        // Set filter object
        $this->instance->resetFilters()->addFilter(new StubFilter());

        self::assertEquals('123123', $this->instance->filter('abc123cba123fgh'));
    }

    /**
     * Method to test getControl().
     *
     * @return void
     */
    #[Test]
    public function getControl(): void
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setControl().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::setControl
     * @TODO   Implement testSetControl().
     */
    #[Test]
    public function setControl(): void
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    #[Test]
    public function addClass(): void
    {
        $this->instance->addClass('foo');

        self::assertEquals('stub-flower foo', $this->instance->getAttribute('class'));

        $this->instance->addClass('stub-flower bar');

        self::assertEquals('stub-flower foo bar', $this->instance->getAttribute('class'));

        $this->instance->addClass('yoo', 'goo');

        self::assertEquals('stub-flower foo bar yoo goo', $this->instance->getAttribute('class'));
    }

    #[Test]
    public function removeClass(): void
    {
        $this->instance->addClass('foo bar yoo goo');

        $this->instance->removeClass('bar');

        self::assertEquals('stub-flower foo yoo goo', $this->instance->getAttribute('class'));

        $this->instance->removeClass('yoo goo');

        self::assertEquals('stub-flower foo', $this->instance->getAttribute('class'));

        $this->instance->removeClass('foo', 'yoo');

        self::assertEquals('stub-flower', $this->instance->getClass());
    }

    /**
     * Method to test getAttribute().
     *
     * @return void
     */
    #[Test]
    public function getAttribute(): void
    {
        $field = new StubField(
            'flower',
            'Flower',
            [
                'placeholder' => 'The Flower',
                'class' => 'stub-flower',
                'required' => true,
            ]
        );

        self::assertEquals('stub-flower', $field->getAttribute('class'));
        self::assertEquals('default', $field->getAttribute('host') ?? 'default');
        self::assertEquals(null, $field->getAttribute('host'));
    }

    /**
     * Method to test setAttribute().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::setAttribute
     */
    #[Test]
    public function setAttribute(): void
    {
        $this->instance->setAttribute('host', 'localhost');

        self::assertEquals('localhost', $this->instance->getAttribute('host', 'default'));
    }

    /**
     * Method to test get().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::get
     */
    #[Test]
    public function get(): void
    {
        // Remove the following lines when you implement this test.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getAttributes().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Field\AbstractField::getAttributes
     */
    #[Test]
    public function getAttributes(): void
    {
        self::assertEquals(
            ['placeholder' => 'The Flower', 'class' => 'stub-flower'],
            $this->instance->getAttributes()
        );
    }
}
