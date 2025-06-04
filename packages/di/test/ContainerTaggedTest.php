<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\DI\Container;
use Windwalker\DI\Exception\DefinitionNotFoundException;
use Windwalker\DI\Test\Stub\StubInstantTaggedService;
use Windwalker\DI\Test\Stub\StubLangCode;
use Windwalker\Utilities\Reflection\ReflectAccessor;

class ContainerTaggedTest extends TestCase
{
    protected Container $instance;

    public function testSetAndGet(): void
    {
        $this->instance->set(StubLangCode::class, 'en');
        $this->instance->set(StubLangCode::class, 'en-US', tag: 'USA');
        $this->instance->set(StubLangCode::class, 'en-GB', tag: 'UK');

        $storages = $this->instance->dump();

        self::assertArrayHasKey(StubLangCode::class, $storages);
        self::assertArrayHasKey(StubLangCode::class . ':USA', $storages);
        self::assertArrayHasKey(StubLangCode::class . ':UK', $storages);

        self::assertEquals('en', $this->instance->get(StubLangCode::class));
        self::assertEquals('en-US', $this->instance->get(StubLangCode::class, tag: 'USA'));
        self::assertEquals('en-GB', $this->instance->get(StubLangCode::class, tag: 'UK'));
    }

    public function testSetFactory(): void
    {
        // Preset tag
        $this->instance->set(
            StubLangCode::class,
            static function (Container $container, string $tag) {
                return new StubLangCode($tag);
            },
            tag: 'USA'
        );

        self::assertEquals('en-US', $this->instance->get(StubLangCode::class, tag: 'USA')());

        // Test get tag not exists
        try {
            $this->instance->get(StubLangCode::class, tag: 'Spain')();

            self::fail('Should throw DefinitionNotFoundException');
        } catch (DefinitionNotFoundException) {
            self::assertTrue(true);
        }
    }

    public function testTaggingWhenGet(): void
    {
        $this->instance->set(
            StubLangCode::class,
            static function (Container $container, string $tag) {
                return new StubLangCode($tag);
            },
        );

        self::assertEquals('ja-JP', $this->instance->get(StubLangCode::class, tag: 'Japan')());
        self::assertEquals('vi-VN', $this->instance->get(StubLangCode::class, tag: 'Vietnam')());
    }

    public function testServiceAttribute(): void
    {
        $service = $this->instance->get(StubInstantTaggedService::class, tag: 'foo');

        // Get again
        $service2 = $this->instance->get(StubInstantTaggedService::class, tag: 'foo');

        self::assertSame($service, $service2);

        try {
            $this->instance->get(StubInstantTaggedService::class);

            self::fail('Should throw DefinitionNotFoundException');
        } catch (DefinitionNotFoundException) {
            self::assertTrue(true);
        }
    }

    public function testCreateObject(): void
    {
        $lang = $this->instance->createObject(StubLangCode::class, args: ['tag' => 'Germany'], tag: 'Germany');
        $lang2 = $this->instance->get(StubLangCode::class, tag: 'Germany');

        self::assertNotSame($lang, $lang2);
        self::assertEquals($lang(), $lang2());

        try {
            $this->instance->get(StubLangCode::class, tag: 'Korea');

            self::fail('Should throw DefinitionNotFoundException');
        } catch (DefinitionNotFoundException) {
            self::assertTrue(true);
        }
    }

    public function testCreateSharedObject(): void
    {
        $lang = $this->instance->createSharedObject(StubLangCode::class, args: ['tag' => 'Germany'], tag: 'Germany');
        $lang2 = $this->instance->get(StubLangCode::class, tag: 'Germany');

        self::assertSame($lang, $lang2);
        self::assertEquals($lang(), $lang2());

        try {
            $this->instance->get(StubLangCode::class, tag: 'Korea');

            self::fail('Should throw DefinitionNotFoundException');
        } catch (DefinitionNotFoundException) {
            self::assertTrue(true);
        }
    }

    public function testPrepareObject(): void
    {
        $this->instance->prepareObject(StubLangCode::class, tag: 'Germany');
        $lang = $this->instance->get(StubLangCode::class, tag: 'Germany');
        $lang2 = $this->instance->get(StubLangCode::class, tag: 'Germany');

        self::assertNotSame($lang, $lang2);
        self::assertEquals($lang(), $lang2());

        // prepareObject() cannot send tag to constructor, so it will use default value.
        self::assertEquals($lang(), 'en-US');

        try {
            $this->instance->get(StubLangCode::class, tag: 'Korea');

            self::fail('Should throw DefinitionNotFoundException');
        } catch (DefinitionNotFoundException) {
            self::assertTrue(true);
        }
    }

    public function testPrepareSharedObject(): void
    {
        $this->instance->prepareSharedObject(StubLangCode::class, tag: 'Germany');
        $lang = $this->instance->get(StubLangCode::class, tag: 'Germany');
        $lang2 = $this->instance->get(StubLangCode::class, tag: 'Germany');

        self::assertSame($lang, $lang2);
        self::assertEquals($lang(), $lang2());

        // prepareObject() cannot send tag to constructor, so it will use default value.
        self::assertEquals($lang(), 'en-US');

        try {
            $this->instance->get(StubLangCode::class, tag: 'Korea');

            self::fail('Should throw DefinitionNotFoundException');
        } catch (DefinitionNotFoundException) {
            self::assertTrue(true);
        }
    }

    public function testExtends(): void
    {
        $this->instance->prepareSharedObject(StubLangCode::class);
        $this->instance->extend(
            StubLangCode::class,
            static function (StubLangCode $lang, Container $container, ?string $tag = null) {
                $lang->tag = $tag;

                return $lang;
            },
            tag: 'Germany'
        );
        $lang = $this->instance->get(StubLangCode::class, tag: 'Germany');

        self::assertEquals('de-DE', $lang());
    }

    public function setUp(): void
    {
        $this->instance = new Container();
    }
}
