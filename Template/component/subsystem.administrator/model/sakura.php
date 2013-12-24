<?php

use Windwalker\Model\AdminModel;

/**
 * Class FlowerModelSakura
 *
 * @since 1.0
 */
class FlowerModelSakura extends AdminModel
{
	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   3.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		return $this->loadForm('com_flower.sakura.form', 'sakura');
	}
}
