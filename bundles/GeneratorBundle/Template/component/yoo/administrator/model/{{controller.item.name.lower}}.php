<?php

use Windwalker\Model\AdminModel;

/**
 * Class {{extension.name.cap}}Model{{controller.item.name.cap}}
 *
 * @since 1.0
 */
class {{extension.name.cap}}Model{{controller.item.name.cap}} extends AdminModel
{
	/**
	 * Method to set new item ordering as first or last.
	 *
	 * @param   JTable $table    Item table to save.
	 * @param   string $position 'first' or other are last.
	 *
	 * @return  void
	 */
	public function setOrderPosition($table, $position = 'last')
	{
		parent::setOrderPosition($table, $position);
	}
}
