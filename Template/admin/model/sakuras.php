<?php

use Windwalker\Model\Model;

/**
 * Class FlowerModelSakuras
 *
 * @since 1.0
 */
class FlowerModelSakuras extends Model
{
	public function getItems()
	{
		$q = $this->db->getQuery(true);

		$q->select('*')
			->from('#__flower_sakuras');

		return $this->db
			->setQuery($q)
			->loadObjectList();
	}
}
