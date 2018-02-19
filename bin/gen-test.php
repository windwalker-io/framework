<?php
/**
 * Part of windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later. see LICENSE
 */

use Windwalker\Application\AbstractCliApplication;
use Windwalker\Console\Prompter\ValidatePrompter;
use Windwalker\Filesystem\Folder;
use Windwalker\Filesystem\Path;
use Windwalker\String\SimpleTemplate;
use Windwalker\String\StringNormalise;
use Windwalker\Structure\Structure;

include_once __DIR__ . '/../vendor/autoload.php';

define('WINDWALKER_ROOT', realpath(__DIR__ . '/..'));

/**
 * Class GenTest
 *
 * @since 1.0
 */
class GenTest extends AbstractCliApplication
{
    /**
     * Execute the controller.
     *
     * @return  boolean  True if controller finished execution, false if the controller did not
     *                   finish execution. A controller might return false if some precondition for
     *                   the controller to run has not been satisfied.
     *
     * @throws  \LogicException
     * @throws  \RuntimeException
     */
    public function doExecute()
    {
        $package = $this->io->getArgument(0, new ValidatePrompter('Enter package name: '));
        $class   = $this->io->getArgument(1, new ValidatePrompter('Enter class name: '));
        $class   = StringNormalise::toClassNamespace($class);
        $target  = $this->io->getArgument(2, $class . 'Test');
        $target  = StringNormalise::toClassNamespace($target);
        $package = ucfirst($package);

        if (!class_exists($class)) {
            $class = 'Windwalker\\' . $package . '\\' . $class;
        }

        if (!class_exists($class)) {
            $this->out('Class not exists: ' . $class);

            exit();
        }

        $replace = new Structure;

        $ref = new \ReflectionClass($class);

        $replace['origin.class.dir']       = dirname($ref->getFileName());
        $replace['origin.class.file']      = $ref->getFileName();
        $replace['origin.class.name']      = $ref->getName();
        $replace['origin.class.shortname'] = $ref->getShortName();
        $replace['origin.class.namespace'] = $ref->getNamespaceName();

        $replace['test.dir'] = WINDWALKER_ROOT . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $package . DIRECTORY_SEPARATOR . 'Test';

        $replace['test.class.name']      = $replace['origin.class.namespace'] . '\\Test\\' . $target;
        $replace['test.class.file']      = Path::clean($replace['test.dir'] . DIRECTORY_SEPARATOR . $target . '.php');
        $replace['test.class.dir']       = dirname($replace['test.class.file']);
        $replace['test.class.shortname'] = $this->getShortname(StringNormalise::toClassNamespace($replace['test.class.name']));
        $replace['test.class.namespace'] = $this->getNamespace($replace['test.class.name']);

        $methods     = $ref->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);
        $methodTmpl  = file_get_contents(WINDWALKER_ROOT . '/bin/templates/test/testMethod.tpl');
        $methodCodes = [];

        foreach ($methods as $method) {
            if ($method->getDeclaringClass()->getName() != $replace['origin.class.name']) {
                continue;
            }

            $replace['origin.method'] = $method->getName();
            $replace['test.method']   = ucfirst($method->getName());

            $methodCodes[] = SimpleTemplate::render($methodTmpl, $replace->toArray());
        }

        $replace['test.methods'] = implode("", $methodCodes);

        $this->genClass($replace);

        $this->out(sprintf(
            'Generate test class: <info>%s</info> to file: <info>%s</info>',
            $replace['test.class.name'],
            $replace['test.class.file']
        ));

        return true;
    }

    /**
     * genClass
     *
     * @param Structure $replace
     *
     * @return  void
     */
    protected function genClass(Structure $replace)
    {
        $tmpl = file_get_contents(WINDWALKER_ROOT . '/bin/templates/test/testClass.tpl');

        $file = SimpleTemplate::render($tmpl, $replace->toArray());

        Folder::create(dirname($replace['test.class.file']));

        file_put_contents($replace['test.class.file'], $file);
    }

    /**
     * getShortname
     *
     * @param string $class
     *
     * @return  mixed
     */
    protected function getShortname($class)
    {
        $class = explode('\\', $class);

        return array_pop($class);
    }

    /**
     * getNamespace
     *
     * @param string $class
     *
     * @return  string
     */
    protected function getNamespace($class)
    {
        $class = explode('\\', $class);

        array_pop($class);

        return implode('\\', $class);
    }
}

$app = new GenTest;

$app->execute();
