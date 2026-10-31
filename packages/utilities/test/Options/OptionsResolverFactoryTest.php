<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Options;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Options\OptionsResolverFactory;

class OptionsResolverFactoryTest extends TestCase
{
    #[Test]
    public function getByClassCreatesOneResolverPerClass(): void
    {
        $class = 'Stub\\FactoryTarget' . uniqid();

        self::assertFalse(OptionsResolverFactory::has($class));

        $resolver = OptionsResolverFactory::getByClass($class);

        self::assertTrue(OptionsResolverFactory::has($class));
        self::assertSame($resolver, OptionsResolverFactory::getByClass($class));
        self::assertNotSame($resolver, OptionsResolverFactory::getByClass($class . 'Other'));
    }
}
