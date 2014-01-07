<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
use Windwalker\View\Html\HtmlView;

/**
 * Class FlowerViewSakurasConfig
 *
 * @since 1.0
 */
class FlowerViewSakurasConfig
{
	/**
	 * @var  array  Property buttonSet.
	 */
	public $buttonSet = array();

	/**
	 * Constructor
	 */
	public function __construct(HtmlView $view)
	{
		$this->buttonSet = array(
			'add'        => 'addNew',
			'edit'       => function() use($itemName)
				{
					\JToolBarHelper::editList($itemName . '.edit');
				},
			'duplicate'  => array(
				'code' => function() use($listName)
					{
						\JToolBarHelper::custom($listName . '.batch.copy', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
					},
				'access' => 'core.create'
			),
			'publish'    => 'publish',
			'ubpublish'  => 'unpublish',
			'checkin'    => 'checkin',
			'delete'     => 'deleteList',
			'trash'      => 'trash',
			'batch'      => 'batch',
			// 100 => 'preferences',
		);
	}
}
