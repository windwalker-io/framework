<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Options;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Windwalker\Utilities\Options\OptionsResolverTrait;

class OptionsResolverTraitTest extends TestCase
{
    protected function createSubject(): object
    {
        return new class () {
            use OptionsResolverTrait {
                resolveOptions as public;
            }
        };
    }

    #[Test]
    public function resolveOptionsAppliesHandlerDefaults(): void
    {
        $subject = $this->createSubject();

        $subject->resolveOptions(['a' => 1], function (OptionsResolver $resolver) {
            $resolver->setDefaults(['a' => 0, 'b' => 2]);
        });

        self::assertEquals(['a' => 1, 'b' => 2], $subject->getOptions());
    }

    #[Test]
    public function resolveOptionsRejectsUndefinedOptions(): void
    {
        $subject = $this->createSubject();

        $this->expectException(UndefinedOptionsException::class);

        $subject->resolveOptions(['a' => 1]);
    }

    #[Test]
    public function resolveOptionsAcceptsEmptyOptionsWithoutHandler(): void
    {
        $subject = $this->createSubject();

        $subject->resolveOptions();

        self::assertSame([], $subject->getOptions());
    }

    #[Test]
    public function getAndSetOptionAccessSingleValues(): void
    {
        $subject = $this->createSubject();

        self::assertSame($subject, $subject->setOption('a', 1));
        self::assertSame(1, $subject->getOption('a'));
        self::assertSame(9, $subject->getOption('none', 9));
    }
}
