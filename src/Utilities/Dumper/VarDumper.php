<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    __LICENSE__
 */

namespace Windwalker\Utilities\Dumper;

use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

/**
 * The VarDumper class.
 *
 * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
     */
    public static function dump($var, int $depth = 5): string
    {
        if (null === self::$handler) {
            $cloner = new VarCloner();
            $cloner->setMaxItems(-1);
            $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);

            if (isset($_SERVER['VAR_DUMPER_FORMAT'])) {
                $dumper = 'html' === $_SERVER['VAR_DUMPER_FORMAT'] ? new PrintRDumper() : new PrintRDumper();
            } else {
                $dumper = \in_array(\PHP_SAPI, ['cli', 'phpdbg']) ? new PrintRDumper() : new PrintRDumper();
            }

            self::$handler = static function ($var) use ($cloner, $dumper, $depth) {
                $dumper->setIndentPad('    ');
                $dumper->dump($cloner->cloneVar($var)->withMaxDepth($depth));
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
     * @since  __DEPLOY_VERSION__
     */
    public static function setHandler(callable $callable = null)
    {
        $prevHandler = self::$handler;
        self::$handler = $callable;

        return $prevHandler;
    }
}
