<?php

namespace Flower\Component;

use Windwalker\Component\Component;
use Windwalker\Debugger\Debugger;
use Windwalker\Helper\LanguageHelper;
use Windwalker\Helper\ProfilerHelper;

/**
 * Class FlowerComponent
 *
 * @since 1.0
 */
abstract class FlowerComponent extends Component
{
	/**
	 * Property name.
	 *
	 * @var string
	 */
	protected $name = 'Flower';

	/**
	 * prepare
	 *
	 * @return  void
	 */
	protected function prepare()
	{
		// Legacy debug
		define('AKDEBUG', true);

		if (JDEBUG)
		{
			Debugger::registerWhoops();
		}

		// Load language
		$lang = $this->container->get('language');

		LanguageHelper::loadAll($lang->getTag(), $this->option);

		parent::prepare();
	}

	/**
	 * postExecute
	 *
	 * @param mixed $result
	 *
	 * @return  mixed
	 */
	protected function postExecute($result)
	{
		// Debug profiler
		if (JDEBUG)
		{
			$result .= "<hr />" . ProfilerHelper::render('Windwalker', true);
		}

		return parent::postExecute($result);
	}
}
