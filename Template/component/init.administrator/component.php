<?php

use Windwalker\Component\Component;
use Windwalker\Helper\LanguageHelper;

/**
 * Class FlowerComponent
 *
 * @since 1.0
 */
final class FlowerComponent extends Component
{
	/**
	 * Property name.
	 *
	 * @var string
	 */
	protected $name = 'Flower';

	/**
	 * Property defaultController.
	 *
	 * @var string
	 */
	protected $defaultController = 'sakuras.display';

	/**
	 * init
	 *
	 * @return void
	 */
	protected function prepare()
	{
		// Load language
		$lang = $this->container->get('language');

		LanguageHelper::loadAll($lang->getTag(), $this->option);
	}
}
