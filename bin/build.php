<?php

use Asika\SimpleConsole\Console;
use Asika\SimpleConsole\ExecResult;

include_once __DIR__ . '/Console.php';

/**
 * Class Build to build subtrees.
 *
 * @since 1.0
 */
class Build extends Console
{
    protected string $organization = 'windwalker-io';

    protected string $branch;

    protected string $tag;

    /**
     * Property subtrees.
     *
     * @var  array
     */
    protected array $subtrees = [
        'attributes' => 'Attributes',
        'authentication' => 'Authentication',
        'authorization' => 'Authorization',
        'cache' => 'Cache',
        // 'console' => 'Console',
        'crypt' => 'Crypt',
        'data' => 'Data',
        'database' => 'Database',
        // 'datamapper' => 'DataMapper',
        'di' => 'DI',
        'dom' => 'Dom',
        'edge' => 'Edge',
        'environment' => 'Environment',
        'event' => 'Event',
        'filesystem' => 'Filesystem',
        'filter' => 'Filter',
        'form' => 'Form',
        'html' => 'Html',
        'http' => 'Http',
        'promise' => 'Promise',
        'language' => 'Language',
        'orm' => 'ORM',
        'pool' => 'Pool',
        'query' => 'Query',
        'queue' => 'Queue',
        'reactor' => 'Reactor',
        'renderer' => 'Renderer',
        'scalars' => 'Scalars',
        'session' => 'Session',
        'stream' => 'Stream',
        'test' => 'Test',
        'uri' => 'Uri',
        'utilities' => 'Utilities',
        // 'validator' => 'Validator',
    ];

    protected bool $dryRun = false;

    protected bool $force = false;

    protected function configure(): void
    {
        $this->addParameter('allows', static::ARRAY)
            ->description('Only allow these subtrees to be pushed.');

        $this->addParameter('--tag|-t', static::STRING)
            ->description('Git tag of this build, will push to main repo and every subtree.');

        $this->addParameter('--branch|-b', static::STRING)
            ->description('Get branch to push, will  push to main repo and every subtree.')
            ->default('test');

        $this->addParameter('--force|-f', static::BOOLEAN)
            ->description('Override commits or not.');

        $this->addParameter('--dry-run', static::BOOLEAN)
            ->description('Do not real push, just run the subtree split process.');
    }

    protected function doExecute(): int
    {
        $this->tag = $tag = $this->get('tag');

        if ($this->tag === 'master') {
            throw new \RuntimeException('Do not use master now');
        }

        $this->dryRun = (bool) $this->get('dry-run');
        $branch = (string) $this->get('branch');

        $this->force = $force = (bool) $this->get('force');

        $force = $force ? ' -f' : false;

        $this->branch = $branch;

        $this->exec('git fetch origin');

        $this->exec('git branch -D ' . $branch);

        $this->exec('git checkout -b ' . $branch);

        // $this->exec('git merge master');

        if ($this->tag) {
            $this->exec('git tag -d ' . $tag);

            $this->exec('git push origin :refs/tags/' . $tag);

            $this->exec('git tag ' . $tag);

            $this->exec(sprintf('git push origin %s' . $force, $this->tag));
        }

        $this->exec(sprintf('git push origin %s:%s' . $force, $branch, $branch));
        // $this->exec(sprintf('git push origin %s %s:%s master:master' . $force, $tag, $branch, $branch));

        $allows = $this->get('allows');

        foreach ($this->subtrees as $subtree => $namespace) {
            if ($allows && !in_array($subtree, $allows, true)) {
                continue;
            }

            if (!is_dir(__DIR__ . '/../packages/' . $subtree)) {
                continue;
            }

            $this->splitTree($subtree, $namespace);
        }

        $this->exec('git checkout ' . $branch);

        $this->writeln()->writeln('Split finish.');

        return static::SUCCESS;
    }

    /**
     * Split Git subTree.
     *
     * @param  string  $subtree
     * @param  string  $namespace
     *
     * @return  void
     */
    protected function splitTree($subtree, $namespace)
    {
        $this->writeln()->writeln(sprintf('@ Start subtree split (%s)', $subtree))
            ->writeln('---------------------------------------');

        // Do split
        $this->exec(sprintf('git branch -D sub-%s', $subtree));
        $this->exec('git subtree split -P packages/' . $subtree . ' -b sub-' . $subtree);

        // Create a new branch
        $this->exec(sprintf('git branch -D %s-%s', $this->branch, $subtree));

        // Add remote repo
        $repo = sprintf(
            'git@github.com:%s/%s.git',
            $this->organization,
            $subtree
        );

        $force = $this->force ? ' -f' : false;

        if (!$force) {
            $this->exec(
                sprintf(
                    'git pull %s %s',
                    $repo,
                    $this->branch
                )
            );
        }

        $this->exec(sprintf('git push %s sub-%s:%s ' . $force, $repo, $subtree, $this->branch));

        if ($this->tag) {
            $this->exec('git checkout sub-' . $subtree);

            $this->exec(sprintf('git tag -d %s', $this->tag));

            $this->exec(sprintf('git push %s :refs/tags/%s', $repo, $this->tag));

            $this->exec(sprintf('git tag %s', $this->tag));

            $this->exec(sprintf('git push %s %s', $repo, $this->tag));
        }

        $this->exec('git checkout ' . $this->branch);
        $this->exec('git branch -D sub-' . $subtree);
    }

    public function exec(string $cmd, \Closure|null|false $output = null, bool $showCmd = true): ExecResult
    {
        if ($this->dryRun) {
            return new ExecResult(0, '', '');
        }

        return parent::exec($cmd, $output, $showCmd);
    }
}

new Build()->execute($argv);
