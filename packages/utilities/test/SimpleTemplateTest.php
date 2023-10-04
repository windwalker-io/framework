<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\SimpleTemplate;

/**
 * Test class of SimpleTemplate
 *
 * @since 3.0
 */
class SimpleTemplateTest extends TestCase
{
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
     * Method to test render().
     *
     * @return void
     */
    public function testRender()
    {
        $data['foo']['bar']['baz'] = 'Flower';

        $this->assertEquals('This is Flower', SimpleTemplate::render('This is {{ foo.bar.baz }}', $data));
        $this->assertEquals('This is ', SimpleTemplate::render('This is {{ foo.yoo }}', $data));
    }

    public function testRenderTemplate(): void
    {
        $tmpl = SimpleTemplate::create('This is [ foo/bar/baz ]')
            ->setDelimiter('/')
            ->setVarWrapper('[', ']');

        $this->assertEquals(
            'This is Flower',
            $tmpl(
                ['foo' => ['bar' => ['baz' => 'Flower']]]
            )
        );

        $this->assertEquals(
            'This is Mountain',
            $tmpl(
                ['foo' => ['bar' => ['baz' => 'Mountain']]]
            )
        );
    }
}
