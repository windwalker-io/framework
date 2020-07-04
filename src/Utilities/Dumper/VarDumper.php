<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Utilities\Dumper;

use Symfony\Component\VarDumper\Caster\DateCaster;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Windwalker\Utilities\Dumper\Caster\DateTimeCaster;

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
     * dump
     *
     * @param mixed $var
     * @param int   $depth
     *
     * @return  string
     *
     * @since  3.5.6
     */
    public static function dump($var, int $depth = 5): string
    {
        if (null === self::$handler) {
            $cloner = new VarCloner();
            $cloner->setMaxItems(-1);
            $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);
            $cloner->addCasters(
                [
                    \DateTimeInterface::class => [DateTimeCaster::class, 'castDateTime']
                ]
            );

//            if (isset($_SERVER['VAR_DUMPER_FORMAT'])) {
//                $dumper = 'html' === $_SERVER['VAR_DUMPER_FORMAT'] ? new PrintRDumper() : new PrintRDumper();
//            } else {
//                $dumper = \in_array(\PHP_SAPI, ['cli', 'phpdbg']) ? new PrintRDumper() : new PrintRDumper();
//            }

            $dumper = new PrintRDumper();

            self::$handler = static function ($var) use ($cloner, $dumper, $depth) {
                $dumper->setIndentPad('    ');

                $result = $dumper->dump(
                    $cloner->cloneVar($var)->withMaxDepth($depth),
                    true
                );

                return substr($result, 0, -1);
            };
        }

        return (string) (self::$handler)($var);
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
    public static function setHandler(callable $callable = null)
    {
        $prevHandler   = self::$handler;
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
