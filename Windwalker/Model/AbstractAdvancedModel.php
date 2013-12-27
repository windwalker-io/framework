<?php

namespace Windwalker\Model;

/**
 * Class AbstractAdvancedModel
 *
 * @since 1.0
 */
abstract class AbstractAdvancedModel extends Model
{
	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $context = '';

	/**
	 * Constructor
	 *
	 * @param array            $config
	 * @param \JRegistry       $state
	 * @param \JDatabaseDriver $db
	 */
	public function __construct($config = array(), \JRegistry $state = null, \JDatabaseDriver $db = null)
	{
		// Guess the context as Option.ModelName.
		if (empty($this->context))
		{
			$this->context = strtolower($this->option . '.' . $this->getName());
		}

		parent::__construct($config, $state, $db);
	}
}
