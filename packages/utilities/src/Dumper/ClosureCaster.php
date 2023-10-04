<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Dumper;

use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Caster\LinkStub;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\Stub;

/**
 * The ClosureCaster class.
 */
class ClosureCaster
{
    public static function castClosure(\Closure $c, array $a, Stub $stub, bool $isNested, int $filter = 0)
    {
        $prefix = Caster::PREFIX_VIRTUAL;
        $c = new \ReflectionFunction($c);

        $a = ReflectionCaster::castFunctionAbstract($c, $a, $stub, $isNested, $filter);

        if (!str_contains($c->name, '{closure}')) {
            $stub->class = isset($a[$prefix . 'class']) ? $a[$prefix . 'class']->value . '::' . $c->name : $c->name;
            unset($a[$prefix . 'class']);
        }
        unset($a[$prefix . 'extra']);

        $stub->class .= ReflectionCaster::getSignature($a);

        if ($f = $c->getFileName()) {
            $stub->attr['file'] = $f;
            $stub->attr['line'] = $c->getStartLine();
        }

        unset($a[$prefix . 'parameters']);

        if ($filter & Caster::EXCLUDE_VERBOSE) {
            $stub->cut += ($c->getFileName() ? 2 : 0) + \count($a);

            return [];
        }

        if ($f) {
            $a[$prefix . 'file'] = new LinkStub($f . ':' . $c->getStartLine());
            $a[$prefix . 'line'] = $c->getStartLine() . ' to ' . $c->getEndLine();
        }

        return $a;
    }
}
