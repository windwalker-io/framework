<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Dumper;

use Symfony\Component\VarDumper\Cloner\VarCloner;

/**
 * The VarDumper class.
 *
 * @since  3.5.6
 */
class VarDumper
{
    /**
     * Property handler.
     *
     * @var callable
     */
    private static $handler;

    /**
     * @param  mixed  $var
     * @param  int    $depth
     *
     * @return  string
     *
     * @since  3.5.6
     */
    public static function dump(mixed $var, int $depth = 5): string
    {
        if (null === self::$handler) {
            unset(VarCloner::$defaultCasters[\DateTimeInterface::class]);

            $cloner = new VarCloner();
            $cloner->setMaxItems(-1);

            $cloner->addCasters(
                [
                    \DateTimeInterface::class => [DateCaster::class, 'castDateTime'],
                ]
            );

            $dumper = new PrintRDumper();

            self::$handler = static function ($var, $depth) use ($cloner, $dumper) {
                $dumper->setIndentPad('    ');

                $result = $dumper->dump(
                    $cloner->cloneVar($var)->withMaxDepth($depth),
                    true
                );

                return substr($result, 0, -1);
            };
        }

        return (string) (self::$handler)($var, $depth);
    }

    /**
     * setHandler
     *
     * @param callable|null $callable
     *
     * @return  callable
     *
     * @since  3.5.6
     */
    public static function setHandler(?callable $callable = null): callable
    {
        $prevHandler = self::$handler;
        self::$handler = $callable;

        return $prevHandler;
    }

    /**
     * isSupported
     *
     * @return  bool
     *
     * @since  3.5.6
     */
    public static function isSupported(): bool
    {
        return class_exists(VarCloner::class);
    }
}
