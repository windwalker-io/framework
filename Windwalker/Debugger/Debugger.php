<?php

namespace Windwalker\Debugger;

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * Class Debugger

 *
*@since 1.0
 */
abstract class Debugger
{
	/**
	 * Property whoops.
	 *
	 * @var Run
	 */
	static protected $whoops;

	/**
	 * Property handler.
	 *
	 * @var PrettyPageHandler
	 */
	static protected $handler;

	/**
	 * registerWhoops
	 *
	 * @return void
	 */
	public static function registerWhoops()
	{
		\JLoader::registerNamespace('Whoops', WINDWALKER . '/admin/debugger');

		self::$whoops  = new Run;
		self::$handler = new PrettyPageHandler;

		self::$whoops->pushHandler(self::$handler);
		self::$whoops->register();
	}

	/**
	 * add
	 *
	 * @param $label
	 * @param $data
	 *
	 * @return void
	 */
	public static function add($label, $data)
	{
		self::$handler->addDataTable($label . ':' . uniqid(), (array) $data);
	}
}
