<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

include_once __DIR__ . '/../../../vendor/autoload.php';

$compiler = new \Windwalker\Edge\Compiler\EdgeCompiler;

//$compiler->addExtension(new \Windwalker\Edge\Extension\BasicExtension);

echo $compiler->compile(file_get_contents(__DIR__ . '/tmpl.blade.php'));
