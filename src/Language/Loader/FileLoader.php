<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Loader;

/**
 * Class FileLoader
 *
 * @since 2.0
 */
class FileLoader extends AbstractLoader
{
    const MIN = 0;

    const LOW = 100;

    const BELOW_NORMAL = 200;

    const NORMAL = 300;

    const ABOVE_NORMAL = 400;

    const HIGH = 500;

    const MAX = 600;

    /**
     * Property name.
     *
     * @var  string
     */
    protected $name = 'file';

    /**
     * Property paths.
     *
     * @var  null
     */
    protected $paths = null;

    /**
     * Constructor.
     *
     * @param \SplPriorityQueue|string[] $paths
     * @param int                        $priority
     */
    public function __construct($paths = [], $priority = self::NORMAL)
    {
        if (!($paths instanceof \SplPriorityQueue)) {
            $queue = new \SplPriorityQueue();

            foreach ((array) $paths as $path) {
                $queue->insert($path, $priority);
            }

            $paths = $queue;
        }

        $this->paths = $paths;
    }

    /**
     * load
     *
     * @param string $file
     *
     * @throws \RuntimeException
     * @return  null|string
     */
    public function load($file)
    {
        if (!is_file($file)) {
            if (!$found = $this->findFile($file)) {
                $paths = array_values(iterator_to_array(clone $this->paths));

                $paths = implode(" / ", $paths);

                throw new \RuntimeException(sprintf('Language file: %s not found. Paths in queue: %s', $file, $paths));
            }
        } else {
            $found = $file;
        }

        return file_get_contents($found);
    }

    /**
     * findFile
     *
     * @param string $file
     *
     * @return  boolean|string
     */
    protected function findFile($file)
    {
        foreach (clone $this->paths as $path) {
            $filePath = $path . '/' . $file;

            if (is_file($filePath)) {
                return $filePath;
            }
        }

        return false;
    }
}
