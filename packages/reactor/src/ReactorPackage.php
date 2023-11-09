<?php

declare(strict_types=1);

namespace Windwalker\Reactor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Windwalker\Console\IOInterface;
use Windwalker\Core\Generator\Builder\CallbackAstBuilder;
use Windwalker\Core\Generator\CodeGenerator;
use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\DI\Attributes\Autowire;
use Windwalker\Filesystem\Filesystem;
use Windwalker\Filesystem\Path;
use Windwalker\Utilities\Str;

class ReactorPackage extends AbstractPackage
{
    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(static::path('etc/*.php'), 'config');

        $installer->installFiles(
            static::path('resources/servers/swoole_website.php'),
            'servers',
            'swoole_website',
            function (string $src, string $dest, IOInterface $io) {
                $this->addMainFileToServers(
                    'swoole',
                    'website',
                    'swoole_website.php',
                    $io
                );
            }
        );

        $installer->installFiles(
            static::path('resources/servers/swoole_websocket.php'),
            'servers',
            'swoole_websocket',
            function (
                string $src,
                string $dest,
                bool $force,
                IOInterface $io,
                #[Autowire] CodeGenerator $codeGenerator
            ) {
                $this->addMainFileToServers(
                    'swoole',
                    'websocket',
                    'swoole_website.php',
                    $io
                );

                $codeGenerator->from(
                    static::path('views/code/websocket/*')
                )
                    ->replaceTo(
                        WINDWALKER_SOURCE . '/Web/',
                        [
                            'name' => 'WsApplication',
                            'ns' => 'App\\Web',
                        ],
                        $force
                    );
            }
        );
    }

    protected function addMainFileToServers(string $engine, string $name, string $file, IOInterface $io): void
    {
        $filePath = Str::removeLeft(
            Path::normalize(WINDWALKER_SERVERS . '/' . $file, '/'),
            Path::normalize(WINDWALKER_ROOT, '/')
        );
        $filePath = ltrim($filePath, '/');
        $registryFile = WINDWALKER_RESOURCES . '/registry/servers.php';

        if (is_file($registryFile)) {
            $fileData = file_get_contents($registryFile);
        } else {
            $fileData = "<?php\nreturn [];";
        }

        $builder = new CallbackAstBuilder($fileData);

        // Create engine aub array
        $found = false;
        $level = 0;

        $builder->enterNode(
            function (Node $node) use ($engine, &$level) {
                if ($node instanceof Node\Expr\Array_) {
                    $level++;
                }
            }
        );

        $builder->leaveNode(
            function (Node $node) use ($engine, &$found, &$level) {
                if ($node instanceof Node\Expr\ArrayItem) {
                    if ((string) $node->key->value === $engine) {
                        $found = true;
                    }
                }

                if ($level === 1 && !$found && $node instanceof Node\Expr\Array_) {
                    $node->items[] = new Node\Expr\ArrayItem(
                        new Node\Expr\Array_([]),
                        new Node\Scalar\String_($engine),
                    );
                }
            }
        );

        $builder = new CallbackAstBuilder($builder->process());

        // Register main file
        $engineScope = null;
        $hasChanged = false;

        $builder->enterNode(
            function (Node $node) use (&$engineScope) {
                if (($node instanceof Node\Expr\ArrayItem) && !$engineScope) {
                    $engineScope = (string) $node->key->value;
                }
            }
        );

        $builder->leaveNode(
            function (Node $node) use ($name, $engine, &$engineScope, $filePath, $io, &$hasChanged) {
                if ($node instanceof Node\Expr\ArrayItem) {
                    if (
                        $engineScope === $engine
                        && (string) $node->key->value === $name
                    ) {
                        $io->style()->warning("Server main file: $name => $filePath exists.");

                        return NodeTraverser::STOP_TRAVERSAL;
                    }

                    if ((string) $node->key->value === $engineScope) {
                        $engineScope = null;
                    }
                }

                if ($engineScope === $engine && $node instanceof Node\Expr\Array_) {
                    $node->items[] = new Node\Expr\ArrayItem(
                        new Node\Scalar\String_($filePath),
                        new Node\Scalar\String_($name),
                    );
                    $hasChanged = true;
                }
            }
        );

        $newCode = $builder->process();

        if ($hasChanged) {
            Filesystem::write($registryFile, $newCode);

            $io->writeln(
                "[<info>ADDED</info>] Register <fg=yellow>$engine:$name</> server to file: <info>$registryFile</info>"
            );
        }
    }
}
