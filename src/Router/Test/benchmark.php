<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

include_once __DIR__ . '/../../../vendor/autoload.php';

use Windwalker\Router\Route;

$routes = file_get_contents(__DIR__ . '/fixtures/routes.txt');

$routes = explode("\n", trim($routes));

$routeItems = array_map(
	function ($route)
	{
		$route = trim($route, '/');

		return new Route($route, $route, array('_return' => $route));
	},
	$routes
);

$count = count($routes);

$seq = new \Windwalker\Router\Matcher\SequentialMatcher;

$seq->setRoutes($routeItems)->setDebug(false);

$trie = new \Windwalker\Router\Matcher\TrieMatcher;

$trie->setRoutes($routeItems)->setDebug(false);

$bin = new \Windwalker\Router\Matcher\BinaryMatcher;

$bin->setRoutes($routeItems)->setDebug(false);

$bench = new \Windwalker\Profiler\Benchmark;

$avg = array();

$bench->addTask(
	'Sequential',
	function() use ($seq, $routes, $count, &$avg)
	{
		static $i = 0;

		if ($i + 1 > $count)
		{
			$i = 0;
		}

		$r = $seq->match($routes[$i]);

		if ($r->getName() == trim($routes[$i], '/'))
		{
			echo '.';
			$avg['seq'] += $seq->getCount();
		}

		$i++;
	}
);

$bench->addTask(
	'Binary',
	function() use ($bin, $routes, $count, &$avg)
	{
		static $i = 0;

		if ($i + 1 > $count)
		{
			$i = 0;
		}

		$r = $bin->match($routes[$i]);

		if ($r->getName() == trim($routes[$i], '/'))
		{
			echo '.';
			$avg['bin'] += $bin->getCount();
		}

		$i++;
	}
);

$bench->addTask(
	'Trie',
	function() use ($trie, $routes, $count, &$avg)
	{
		static $i = 0;

		$trie->setTree(unserialize(file_get_contents(__DIR__ . '/fixtures/cache.trie')));

		if ($i + 1 > $count)
		{
			$i = 0;
		}

		$r = $trie->match($routes[$i]);

		if ($r->getName() == trim($routes[$i], '/'))
		{
			echo '.';
			$avg['trie'] += $trie->getCount();
		}

		$i++;
	}
);

$bench->execute(1000);
echo "\n";

echo $avg['seq'] / 1000 . "\n";
echo $avg['bin'] / 1000 . "\n";
echo $avg['trie'] / 1000 . "\n";

echo $bench->render();
echo "\n";

// file_put_contents(__DIR__ . '/cache.trie', serialize($trie->getTree()));
