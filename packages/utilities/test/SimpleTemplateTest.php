<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\SimpleTemplate;

/**
 * Test class of SimpleTemplate
 */
class SimpleTemplateTest extends TestCase
{
    #[Test]
    public function renderReplacesNestedPlaceholderWithData(): void
    {
        $data['foo']['bar']['baz'] = 'Flower';

        $this->assertEquals('This is Flower', SimpleTemplate::render('This is {{ foo.bar.baz }}', $data));
    }

    #[Test]
    public function renderReplacesMissingPlaceholderWithEmptyString(): void
    {
        $data['foo']['bar']['baz'] = 'Flower';

        $this->assertEquals('This is ', SimpleTemplate::render('This is {{ foo.yoo }}', $data));
    }

    #[Test]
    public function invokedTemplateUsesCustomDelimiterAndWrapperForEachData(): void
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
