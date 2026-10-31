<?php

declare(strict_types=1);

namespace Windwalker\Form\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Form\Field\TextField;
use Windwalker\Form\Form;
use Windwalker\Utilities\Symbol;
use Windwalker\Form\Test\Mock\MockFormRenderer;
use Windwalker\Form\Test\Stub\StubAttrDefinition;
use Windwalker\Form\Test\Stub\StubFieldDefinition;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Form\Field\PasswordField;

/**
 * Test class of Form
 *
 * @since 2.0
 */
class FormTest extends TestCase
{
    use BaseAssertionTrait;

    /**
     * Test instance.
     *
     * @var Form
     */
    protected $instance;

    /**
     * getByDefine
     *
     * @param  string  $ns
     *
     * @return  Form
     */
    protected function getByDefine($ns = ''): Form
    {
        $form = new Form($ns);

        $form->defineFormFields(new StubFieldDefinition());

        return $form;
    }

    /**
     * Method to test addFields().
     *
     * @return void
     */
    #[Test]
    public function addAndGetFields(): void
    {
        $form = new Form();

        $form->addFields(
            [
                new TextField('foo'),
                new TextField('bar'),
            ]
        );

        $fields = iterator_to_array($form->getFields());

        self::assertInstanceOf('Windwalker\\Form\\Field\\TextField', $fields['foo']);
        self::assertEquals('bar', $fields['bar']->getName());

        // Test fieldset
        $form->addFields(
            [
                new TextField('bird'),
                new TextField('rabbit'),
            ],
            'flower'
        );

        $fields = iterator_to_array($form->getFields('flower'));

        self::assertInstanceOf(TextField::class, $fields['bird']);
        self::assertEquals('rabbit', $fields['rabbit']->getName());

        // Test Group
        $form->addFields(
            [
                new TextField('egg'),
                new TextField('hotdog'),
            ],
            'rose',
            'sakura'
        );

        $fields = iterator_to_array($form->getFields(null, 'sakura'));

        self::assertInstanceOf(TextField::class, $fields['sakura/egg']);
        self::assertEquals('sakura[hotdog]', $fields['sakura/hotdog']->getInputName());
    }

    /**
     * Method to test addField().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::addField
     */
    #[Test]
    public function addAndGetField(): void
    {
        $form = new Form();

        $form->addField(new TextField('foo'));

        self::assertEquals('foo', $form->getField('foo')->getInputName());

        $form->addField(new TextField('bar'), 'flower');

        self::assertEquals('bar', $form->getField('bar')->getInputName());

        $form->addField(new TextField('yoo'), null, 'sakura');

        self::assertEquals('sakura[yoo]', $form->getField('sakura/yoo')->getInputName());
    }

    #[Test]
    public function getIterator(): void
    {
        $form = new Form();
        $form->addFields([new TextField('foo'), new TextField('bar')]);

        self::assertSame(['foo', 'bar'], array_keys(iterator_to_array($form)));
    }

    /**
     * Method to test removeField().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::removeField
     */
    #[Test]
    public function removeField(): void
    {
        $form = $this->getByDefine();

        $form->removeField('u/username');

        self::assertNull($form->getField('u/username'));
    }

    /**
     * Method to test fill().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::fill
     */
    #[Test]
    public function fill(): void
    {
        $data = [
            'id' => 5,
            'u' => [
                'username' => 'foo',
            ],
        ];

        $form = $this->getByDefine();

        $fields = iterator_to_array($form->fill($data)->getFields());

        self::assertEquals(5, $fields['id']->getValue());
        self::assertEquals('foo', $fields['u/username']->getValue());
    }

    #[Test]
    public function fillCollection(): void
    {
        $data = \Windwalker\collect(
            [
                'id' => 5,
                'u' => [
                    'username' => 'foo',
                ],
            ]
        );

        $form = $this->getByDefine();

        $fields = iterator_to_array($form->fill($data)->getFields());

        self::assertEquals(5, $fields['id']->getValue());
        self::assertEquals('foo', $fields['u/username']->getValue());
    }

    #[Test]
    public function bind(): void
    {
        $data = [
            'id' => 5,
            'u' => [
                'username' => 'foo',
            ],
        ];

        $form = $this->getByDefine();

        $fields = iterator_to_array($form->bind($data)->getFields());

        $data['id'] = 6;
        $data['u']['username'] = 'bar';

        self::assertEquals(6, $fields['id']->getValue());
        self::assertEquals('bar', $fields['u/username']->getValue());
    }

    /**
     * Method to test validate().
     *
     * @return void
     */
    #[Test]
    public function validate(): void
    {
        $form = $this->getByDefine();

        $data = [
            'id' => 5,
            'u' => [
                'username' => 'foo',
            ],
            'b' => [
                'email' => 'bar@gmail.com',
                'password' => '123_ abc4456qwe:$yui',
            ],
        ];

        $form->bind($data);

        $result = $form->validate($form->filter($data));

        self::assertTrue($result->isSuccess());

        $data = [
            'id' => 5,
            'u' => [
                'username' => 'foo',
            ],
            'b' => [
                'email' => 'bar/gmail.com',
                'password' => '123_ abc4456qwe:$yui',
            ],
        ];

        $result = $form->validate($filtered = $form->filter($data));

        self::assertTrue($result->isFailure());
        self::assertTrue($result->getResult('u/username')->isSuccess());

        // Filter ALNUM
        self::assertEquals('123abc4456qweyui', $filtered['b']['password']);
    }

    /**
     * Method to test prepareView().
     *
     * @return void
     */
    #[Test]
    public function getViews(): void
    {
        $form = $this->getByDefine();

        $data = [
            'id' => 5,
            'u' => [
                'username' => 'foo',
            ],
            'b' => [
                'email' => 'bar/gmail.com',
                'password' => '123_ abc4456qwe:$yui',
            ],
        ];

        $form->bind($data);

        $array = $form->getViews();

        self::assertEquals('123_ abc4456qwe:$yui', $array['Password']);
    }

    #[Test]
    public function prepareStore(): void
    {
        $form = new Form();
        $form->addField(new TextField('foo'));
        $form->addField(new TextField('bar'), null, 'sakura');

        $data = ['foo' => 'a', 'sakura' => ['bar' => 'b'], 'other' => 'c'];

        self::assertSame($data, $form->prepareStore($data));
        self::assertSame(['other' => 'c'], $form->prepareStore(['other' => 'c']));
    }

    /**
     * Method to test renderField().
     *
     * @return void
     *
     * @covers \Windwalker\Form\Form::renderField
     *
     * @since  2.1.2
     */
    #[Test]
    public function renderField(): void
    {
        $form = $this->getByDefine('windwalker');

        $html = '<div id="input-windwalker-id-wrapper" class="control-input" data-field-wrapper><label id="input-windwalker-id-label" data-field-label for="input-windwalker-id">ID</label><div><input id="input-windwalker-id" name="windwalker[id]" data-field-input type="text" value=""></div></div>';

        self::assertEquals($html, $form->renderField('id'));

        // Use renderer

        $form->setRenderer(new MockFormRenderer());

        self::assertEquals(
            '<mock id="input-windwalker-id-wrapper" class="control-input" data-field-wrapper>Hello World: windwalker[id]</mock>',
            $form->renderField('id')
        );
    }

    #[Test]
    public function renderFields(): void
    {
        $form = new Form();
        $form->setRenderer(new MockFormRenderer());
        $form->addField(new TextField('foo'));
        $form->addField(new TextField('bar'), 'flower');

        self::assertStringContainsString('Hello World: foo', $form->renderFields(Symbol::none()));
        self::assertStringNotContainsString('Hello World: bar', $form->renderFields(Symbol::none()));
        self::assertStringContainsString('Hello World: bar', $form->renderFields('flower'));
        self::assertStringNotContainsString('Hello World: foo', $form->renderFields('flower'));
    }

    /**
     * Method to test getControl().
     *
     * @return void
     */
    #[Test]
    public function getAndSetNamespace(): void
    {
        $form = $this->getByDefine();

        $form->setNamespace('wind');

        self::assertEquals('wind[id]', $form->getField('id')->getInputName());
        self::assertEquals('wind', $form->getNamespace());
    }

    /**
     * testFilterAndGetValues
     *
     * @return  void
     *
     * @covers \Windwalker\Form\Form::filter
     * @covers \Windwalker\Form\Form::getValues
     */
    #[Test]
    public function filterAndGetValues(): void
    {
        $form = $this->getByDefine();

        $data = [
            'id' => '123abc',
            'u' => [
                'username' => 'foo',
            ],
            'b' => [
                'email' => 'bar@gmail.com',
                'password' => '123_ abc/\4,456qwe:$yui',
            ],
        ];

        $filtered = $form->filter($data);

        self::assertEquals('123', $filtered['id']);
        self::assertEquals('123abc4456qweyui', $filtered['b']['password']);
    }

    #[Test]
    public function filterWithDefaultValues(): void
    {
        $form = $this->getByDefine();

        $data = [
            'id' => '123abc',
            'u' => [
                'username' => '',
            ],
            'b' => [
                'email' => 'bar@gmail.com',
                'password' => '123_ abc/\4,456qwe:$yui',
            ],
        ];

        $filtered = $form->filter($data, Form::FILTER_USE_DEFAULT_VALUE);

        self::assertEquals('Admin', $filtered['u']['username']);
        self::assertEquals('Default Address', $filtered['address']);
    }

    /**
     * testSetAndGetFieldRendererHandler
     *
     * @return  void
     */
    #[Test]
    public function setAndGetFieldRendererHandler(): void
    {
        $renderer = new MockFormRenderer();

        $form = new Form();
        $form->add('test', new TextField());

        $form->setRenderer($renderer);

        self::assertEquals(
            '<mock id="input-test-wrapper" data-field-wrapper>Hello World: test</mock>',
            trim($form->renderFields())
        );
    }

    #[Test]
    public function attrDefinition(): void
    {
        $define = new StubAttrDefinition();

        $form = new Form();

        $form->defineFormFields($define);

        $fieldsets = $form->getFieldsets();

        self::assertEquals(
            [
                'basic',
                'meta',
                'user'
            ],
            array_keys($fieldsets)
        );

        $fields = [];

        foreach ($form->getFields() as $field) {
            $fields[$field->getName()] = $field::class;
        }

        self::assertEquals(
            [
                'id' => TextField::class,
                'u/username' => TextField::class,
                'email' => TextField::class,
                'password' => PasswordField::class,
                'address' => TextField::class,
            ],
            $fields
        );
    }

    #[Test]
    public function hasFieldAndMustGetField(): void
    {
        $form = new Form();
        $form->addField(new TextField('foo'));

        self::assertTrue($form->hasField('foo'));
        self::assertFalse($form->hasField('bar'));
        self::assertSame($form->getField('foo'), $form->mustGetField('foo'));

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessageIs('Field "bar" not exists.');

        $form->mustGetField('bar');
    }

    #[Test]
    public function arrayAccessReadsChecksAndUnsetsFields(): void
    {
        $form = new Form();
        $form->addField(new TextField('foo'));

        self::assertTrue(isset($form['foo']));
        self::assertInstanceOf(TextField::class, $form['foo']);
        self::assertNull($form['bar']);

        unset($form['foo']);

        self::assertFalse(isset($form['foo']));
    }

    #[Test]
    public function countAndCountFields(): void
    {
        $form = new Form();
        $form->addFields([new TextField('a'), new TextField('b')], 'one');
        $form->addFields([new TextField('c')], 'two', 'ns');

        self::assertCount(3, $form);
        self::assertSame(2, $form->countFields('one'));
        self::assertSame(1, $form->countFields(null, 'ns'));
        self::assertSame(3, $form->countFields());
    }

    #[Test]
    public function removeFieldsByFieldsetOrNamespace(): void
    {
        $form = new Form();
        $form->addFields([new TextField('a'), new TextField('b')], 'one');
        $form->addFields([new TextField('c')], 'two', 'ns');

        $form->removeFields('one');

        self::assertFalse($form->hasField('a'));
        self::assertFalse($form->hasField('b'));
        self::assertTrue($form->hasField('ns/c'));

        $form->removeFields(null, 'ns');

        self::assertCount(0, $form);
    }

    #[Test]
    public function getValuesReturnsNestedFieldValues(): void
    {
        $form = new Form();
        $form->addField(new TextField('foo'));
        $form->addField(new TextField('bar'), null, 'sakura');
        $form->fill(['foo' => 'a', 'sakura' => ['bar' => 'b']]);

        self::assertSame(['foo' => 'a', 'sakura' => ['bar' => 'b']], $form->getValues());
    }

    #[Test]
    public function fillToFillsDataUnderPath(): void
    {
        $form = new Form();
        $form->addField(new TextField('bar'), null, 'sakura');

        $form->fillTo('sakura', ['bar' => 'b']);

        self::assertSame('b', $form->getField('sakura/bar')->getValue());
    }

    #[Test]
    public function getBoundedReturnsReferenceToBoundData(): void
    {
        $form = new Form();
        $form->addField(new TextField('foo'));
        $data = ['foo' => 'a'];

        $form->bind($data);

        self::assertSame(['foo' => 'a'], $form->getBounded());
    }

    #[Test]
    public function fieldsetsCanBeCreatedWithTitleFetchedAndRemoved(): void
    {
        $form = new Form();

        $fieldset = $form->fieldsetWithTitle('basic', 'Basic Info');

        self::assertSame($fieldset, $form->getFieldset('basic'));
        self::assertSame('Basic Info', $fieldset->getTitle());
        self::assertSame($form, $fieldset->getForm());
        self::assertNull($form->getFieldset('none'));

        $form->removeFieldset('basic');

        self::assertNull($form->getFieldset('basic'));
    }

    #[Test]
    public function appendNamespaceNormalizesSeparators(): void
    {
        $form = new Form('wind');

        $form->appendNamespace('/sakura/');

        self::assertSame('wind/sakura', $form->getNamespace());
    }

    #[Test]
    public function groupWrapsFieldsInNamespace(): void
    {
        $form = new Form();

        $form->group(
            'sakura',
            function (Form $form) {
                $form->addField(new TextField('bar'));
            }
        );

        self::assertTrue($form->hasField('sakura/bar'));
    }
}
