<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Record;

/**
 * The NestedRecord class.
 * 
 * @since  2.0
 */
class NestedRecord extends Record
{
	/**
	 * @deprecated  use LOCATION_* instead. Will be remove in 2.1.
	 */
	const POSITION_BEFORE = 1;

	/**
	 * @deprecated  use LOCATION_* instead. Will be remove in 2.1.
	 */
	const POSITION_AFTER = 2;

	/**
	 * @deprecated  use LOCATION_* instead. Will be remove in 2.1.
	 */
	const POSITION_FIRST_CHILD = 3;

	/**
	 * @deprecated  use LOCATION_* instead. Will be remove in 2.1.
	 */
	const POSITION_LAST_CHILD = 4;

	/**
	 * @const integer
	 */
	const LOCATION_BEFORE = 1;

	/**
	 * @const integer
	 */
	const LOCATION_AFTER = 2;

	/**
	 * @const integer
	 */
	const LOCATION_FIRST_CHILD = 3;

	/**
	 * @const integer
	 */
	const LOCATION_LAST_CHILD = 4;

	/**
	 * Object property to hold the location type to use when storing the row.
	 * Possible values are: ['before', 'after', 'first-child', 'last-child'].
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $location;

	/**
	 * Object property to hold the primary key of the location reference node to
	 * use when storing the row.  A combination of location type and reference
	 * node describes where to store the current node in the tree.
	 *
	 * @var    integer
	 * @since  11.1
	 */
	protected $locationId;

	/**
	 * An array to cache values in recursive processes.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $cache = array();

	/**
	 * Cache for the root ID
	 *
	 * @var    integer
	 * @since  3.3
	 */
	protected static $rootId = null;

	/**
	 * Method to get an array of nodes from a given node to its root.
	 *
	 * @param   integer  $pk  Primary key of the node for which to get the path.
	 *
	 * @return  mixed    An array of node objects including the start node.
	 *
	 * @since   11.1
	 * @throws  \RuntimeException on database error
	 */
	public function getPath($pk = null)
	{
		$k = $this->getKeyName();
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the path from the node to the root.
		$query = $this->db->getQuery(true)
			->select('p.' . $k . ', p.parent_id, p.level, p.lft, p.rgt')
			->from($this->qn($this->table) . ' AS n, ' . $this->qn($this->table) . ' AS p')
			->where('n.lft BETWEEN p.lft AND p.rgt')
			->where('n.' . $k . ' = ' . (int) $pk)
			->order('p.lft');

		return $this->db->getReader($query)->loadObjectList();
	}

	/**
	 * Method to get a node and all its child nodes.
	 *
	 * @param   integer  $pk  Primary key of the node for which to get the tree.
	 *
	 * @return  mixed    Boolean false on failure or array of node objects on success.
	 *
	 * @since   11.1
	 * @throws  \RuntimeException on database error.
	 */
	public function getTree($pk = null)
	{
		$k = $this->getKeyName();
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the node and children as a tree.
		$query = $this->db->getQuery(true)
			->select('n.' . $k . ', n.parent_id, n.level, n.lft, n.rgt')
			->from($this->qn($this->table) . ' AS n, ' . $this->qn($this->table) . ' AS p')
			->where('n.lft BETWEEN p.lft AND p.rgt')
			->where('p.' . $k . ' = ' . $pk)
			->order('n.lft');

		return $this->db->getReader($query)->loadObjectList();
	}

	/**
	 * Method to determine if a node is a leaf node in the tree (has no children).
	 *
	 * @param   integer  $pk  Primary key of the node to check.
	 *
	 * @return  boolean  True if a leaf node, false if not or null if the node does not exist.
	 *
	 * @since   11.1
	 * @throws  \RuntimeException on database error.
	 */
	public function isLeaf($pk = null)
	{
		$key  = $this->getKeyName();
		$pk   = (is_null($pk)) ? $this->$key : $pk;
		$node = $this->getNode($pk);

		// Get the node by primary key.
		if (empty($node))
		{
			// Error message set in getNode method.
			return null;
		}

		// The node is a leaf node.
		return (($node->rgt - $node->lft) == 1);
	}

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 *
	 * @since   11.1
	 * @throws  \Exception
	 * @throws  \RuntimeException on database error.
	 * @throws  \UnexpectedValueException
	 */
	public function check()
	{
		// Check that the parent_id field is valid.
		if ($this->parent_id == 0)
		{
			throw new \UnexpectedValueException(sprintf('Invalid `parent_id` [%s] in %s', $this->parent_id, get_class($this)));
		}

		$query = $this->db->getQuery(true)
			->select('COUNT(' . $this->qn($this->getKeyName()) . ')')
			->from($this->table)
			->where($this->getKeyName() . ' = ' . $this->parent_id);

		if (!$this->db->setQuery($query)->loadResult())
		{
			throw new \UnexpectedValueException(sprintf('Invalid `parent_id` [%s] in %s', $this->parent_id, get_class($this)));
		}

		return true;
	}

	/**
	 * Method to set the location of a node in the tree object.  This method does not
	 * save the new location to the database, but will set it in the object so
	 * that when the node is stored it will be stored in the new location.
	 *
	 * @param   integer  $referenceId  The primary key of the node to reference new location by.
	 * @param   integer  $position     Location type string. ['before', 'after', 'first-child', 'last-child']
	 *
	 * @return  static
	 *
	 * @since   11.1
	 * @throws  \InvalidArgumentException
	 */
	public function setLocation($referenceId, $position = self::LOCATION_AFTER)
	{
		$allow = array(
			static::LOCATION_AFTER,
			static::LOCATION_BEFORE,
			static::LOCATION_FIRST_CHILD,
			static::LOCATION_LAST_CHILD
		);

		// Make sure the location is valid.
		if (!in_array($position, $allow))
		{
			throw new \InvalidArgumentException(sprintf('%s::setLocation(%d, *%s*)', get_class($this), $referenceId, $position));
		}

		// Set the location properties.
		$this->location = $position;
		$this->locationId = $referenceId;

		return $this;
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		$k = $this->getKeyName();

		// @onBeforeStore

		/*
		 * If the primary key is empty, then we assume we are inserting a new node into the
		 * tree.  From this point we would need to determine where in the tree to insert it.
		 */
		if (empty($this->$k))
		{
			/*
			 * We are inserting a node somewhere in the tree with a known reference
			 * node.  We have to make room for the new node and set the left and right
			 * values before we insert the row.
			 */
			if ($this->locationId < 0)
			{
				// Negative parent ids are invalid
				throw new \UnexpectedValueException(sprintf('%s::store() used a negative _location_id', get_class($this)));
			}

			// We are inserting a node relative to the last root node.
			if ($this->locationId == 0)
			{
				// Get the last root node as the reference node.
				$query = $this->db->getQuery(true)
					->select($this->getKeyName() . ', parent_id, level, lft, rgt')
					->from($this->table)
					->where('parent_id = 0')
					->order('lft DESC')
					->limit(1);

				$this->db->setQuery($query);

				$reference = $this->db->loadOne();
			}
			// We have a real node set as a location reference.
			else
			{
				// Get the reference node by primary key.
				if (!$reference = $this->getNode($this->locationId))
				{
					throw new \UnexpectedValueException('Cannot get node by location id: ' . $this->locationId);
				}
			}

			// Get the reposition data for shifting the tree and re-inserting the node.
			if (!($repositionData = $this->getTreeRepositionData($reference, 2, $this->location)))
			{
				throw new \UnexpectedValueException('Cannot get reposition data.');
			}

			// Create space in the tree at the new location for the new node in left ids.
			$query = $this->db->getQuery(true)
				->update($this->table)
				->set('lft = lft + 2')
				->where($repositionData->left_where);

			$this->db->setQuery($query)->execute();

			// Create space in the tree at the new location for the new node in right ids.
			$query->clear()
				->update($this->table)
				->set('rgt = rgt + 2')
				->where($repositionData->right_where);

			$this->db->setQuery($query)->execute();

			// Set the object values.
			$this->parent_id = $repositionData->new_parent_id;
			$this->level = $repositionData->new_level;
			$this->lft = $repositionData->new_lft;
			$this->rgt = $repositionData->new_rgt;

		}
		/*
		 * If we have a given primary key then we assume we are simply updating this
		 * node in the tree.  We should assess whether or not we are moving the node
		 * or just updating its data fields.
		 */
		else
		{
			// If the location has been set, move the node to its new location.
			if ($this->locationId > 0)
			{
				$this->moveByReference($this->locationId, $this->location, $this->$k);
			}
		}

		$result = parent::store($updateNulls);

		// @onAfterStore

		return $result;
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   integer  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the
	 *                           ordering values.
	 *
	 * @return  mixed    Boolean true on success.
	 *
	 * @since   11.1
	 */
	public function move($delta, $where = '')
	{
		$k = $this->getKeyName();
		$pk = $this->$k;

		$query = $this->db->getQuery(true)
			->select($k)
			->from($this->table)
			->where('parent_id = ' . $this->q($this->parent_id));

		if ($where)
		{
			$query->where($where);
		}

		if ($delta > 0)
		{
			$query->where('rgt > ' . (int) $this->rgt)
				->order('rgt ASC');

			$position = static::LOCATION_AFTER;
		}
		else
		{
			$query->where('lft < ' . (int) $this->lft)
				->order('lft DESC');

			$position = static::LOCATION_BEFORE;
		}

		$this->db->setQuery($query);
		$referenceId = $this->db->loadResult();

		if ($referenceId)
		{
			return $this->moveByReference($referenceId, $position, $pk);
		}

		return false;
	}

	/**
	 * Method to move a node and its children to a new location in the tree.
	 *
	 * @param   integer  $referenceId  The primary key of the node to reference new location by.
	 * @param   integer  $position     Location type string. ['before', 'after', 'first-child', 'last-child']
	 * @param   integer  $pk           The primary key of the node to move.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  \RuntimeException on database error.
	 */
	public function moveByReference($referenceId, $position = self::LOCATION_AFTER, $pk = null)
	{
		$k = $this->getKeyName();
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the node by id.
		if (!$node = $this->getNode($pk))
		{
			// Error message set in getNode method.
			return false;
		}

		// Get the ids of child nodes.
		$query = $this->db->getQuery(true)
			->select($k)
			->from($this->table)
			->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);

		$children = $this->db->setQuery($query)->loadColumn();

		// Cannot move the node to be a child of itself.
		if (in_array($referenceId, $children))
		{
			throw new \UnexpectedValueException(
				sprintf('%s::moveByReference(%d, %s, %d) parenting to child.', get_class($this), $referenceId, $position, $pk)
			);
		}

		/*
		 * Move the sub-tree out of the nested sets by negating its left and right values.
		 */
		$query->clear()
			->update($this->table)
			->set('lft = lft * (-1), rgt = rgt * (-1)')
			->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);

		$this->db->setQuery($query);

		$this->db->setQuery($query)->execute();

		/*
		 * Close the hole in the tree that was opened by removing the sub-tree from the nested sets.
		 */

		// Compress the left values.
		$query->clear()
			->update($this->table)
			->set('lft = lft - ' . (int) $node->width)
			->where('lft > ' . (int) $node->rgt);

		$this->db->setQuery($query)->setQuery($query)->execute();

		// Compress the right values.
		$query->clear()
			->update($this->table)
			->set('rgt = rgt - ' . (int) $node->width)
			->where('rgt > ' . (int) $node->rgt);

		$this->db->setQuery($query)->setQuery($query)->execute();

		// We are moving the tree relative to a reference node.
		if ($referenceId)
		{
			// Get the reference node by primary key.
			$reference = $this->getNode($referenceId);

			// Get the reposition data for shifting the tree and re-inserting the node.
			$repositionData = $this->getTreeRepositionData($reference, $node->width, $position);
		}
		// We are moving the tree to be the last child of the root node
		else
		{
			// Get the last root node as the reference node.
			$query->clear()
				->select($this->getKeyName() . ', parent_id, level, lft, rgt')
				->from($this->table)
				->where('parent_id = 0')
				->order('lft DESC')
				->limit(1);

			$reference = $this->db->getReader($query)->loadObject();

			// Get the reposition data for re-inserting the node after the found root.
			$repositionData = $this->getTreeRepositionData($reference, $node->width, 'last-child');
		}

		/*
		 * Create space in the nested sets at the new location for the moved sub-tree.
		 */

		// Shift left values.
		$query->clear()
			->update($this->table)
			->set('lft = lft + ' . (int) $node->width)
			->where($repositionData->left_where);

		$this->db->setQuery($query)->setQuery($query)->execute();

		// Shift right values.
		$query->clear()
			->update($this->table)
			->set('rgt = rgt + ' . (int) $node->width)
			->where($repositionData->right_where);

		$this->db->setQuery($query)->setQuery($query)->execute();

		/*
		 * Calculate the offset between where the node used to be in the tree and
		 * where it needs to be in the tree for left ids (also works for right ids).
		 */
		$offset = $repositionData->new_lft - $node->lft;
		$levelOffset = $repositionData->new_level - $node->level;

		// Move the nodes back into position in the tree using the calculated offsets.
		$query->clear()
			->update($this->table)
			->set('rgt = ' . (int) $offset . ' - rgt')
			->set('lft = ' . (int) $offset . ' - lft')
			->set('level = level + ' . (int) $levelOffset)
			->where('lft < 0');

		$this->db->setQuery($query)->setQuery($query)->execute();

		// Set the correct parent id for the moved node if required.
		if ($node->parent_id != $repositionData->new_parent_id)
		{
			$query = $this->db->getQuery(true)
				->update($this->table)
				->set('parent_id = ' . (int) $repositionData->new_parent_id)
				->where($this->getKeyName() . ' = ' . $node->$k);

			$this->db->setQuery($query)->setQuery($query)->execute();
		}

		// Set the object values.
		$this->parent_id = $repositionData->new_parent_id;
		$this->level = $repositionData->new_level;
		$this->lft = $repositionData->new_lft;
		$this->rgt = $repositionData->new_rgt;

		return true;
	}

	/**
	 * Method to delete a node and, optionally, its child nodes from the table.
	 *
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function delete($pk = null, $children = true)
	{
		$k = $this->getKeyName();
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// @onBeforeDelete

		// Get the node by id.
		$node = $this->getNode($pk);

		$query = $this->db->getQuery(true);

		// Should we delete all children along with the node?
		if ($children)
		{
			// Delete the node and all of its children.
			$query->clear()
				->delete($this->table)
				->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);

			$this->db->setQuery($query)->execute();

			// Compress the left values.
			$query->clear()
				->update($this->table)
				->set('lft = lft - ' . (int) $node->width)
				->where('lft > ' . (int) $node->rgt);

			$this->db->setQuery($query)->execute();

			// Compress the right values.
			$query->clear()
				->update($this->table)
				->set('rgt = rgt - ' . (int) $node->width)
				->where('rgt > ' . (int) $node->rgt);

			$this->db->setQuery($query)->execute();
		}
		// Leave the children and move them up a level.
		else
		{
			// Delete the node.
			$query->clear()
				->delete($this->table)
				->where('lft = ' . (int) $node->lft);

			$this->db->setQuery($query)->execute();

			// Shift all node's children up a level.
			$query->clear()
				->update($this->table)
				->set('lft = lft - 1')
				->set('rgt = rgt - 1')
				->set('level = level - 1')
				->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);

			$this->db->setQuery($query)->execute();

			// Adjust all the parent values for direct children of the deleted node.
			$query->clear()
				->update($this->table)
				->set('parent_id = ' . (int) $node->parent_id)
				->where('parent_id = ' . (int) $node->$k);

			$this->db->setQuery($query)->execute();

			// Shift all of the left values that are right of the node.
			$query->clear()
				->update($this->table)
				->set('lft = lft - 2')
				->where('lft > ' . (int) $node->rgt);

			$this->db->setQuery($query)->execute();

			// Shift all of the right values that are right of the node.
			$query->clear()
				->update($this->table)
				->set('rgt = rgt - 2')
				->where('rgt > ' . (int) $node->rgt);

			$this->db->setQuery($query)->execute();
		}

		// @onAfterDelete

		return true;
	}

	/**
	 * Gets the ID of the root item in the tree
	 *
	 * @return  mixed  The primary id of the root row, or false if not found and the internal error is set.
	 *
	 * @since   11.1
	 */
	public function getRootId()
	{
		if (self::$rootId !== null)
		{
			return self::$rootId;
		}

		// Get the root item.
		$k = $this->getKeyName();

		// Test for a unique record with parent_id = 0
		$query = $this->db->getQuery(true)
			->select($k)
			->from($this->table)
			->where('parent_id = 0');

		$result = $this->db->setQuery($query)->loadColumn();

		if (count($result) == 1)
		{
			return self::$rootId = $result[0];
		}

		// Test for a unique record with lft = 0
		$query->clear()
			->select($k)
			->from($this->table)
			->where('lft = 0');

		$result = $this->db->setQuery($query)->loadColumn();

		if (count($result) == 1)
		{
			return self::$rootId = $result[0];
		}

		throw new \UnexpectedValueException(sprintf('%s::getRootId', get_class($this)));
	}

	/**
	 * Method to recursively rebuild the whole nested set tree.
	 *
	 * @param   integer  $parentId  The root of the tree to rebuild.
	 * @param   integer  $leftId    The left id to start with in building the tree.
	 * @param   integer  $level     The level to assign to the current nodes.
	 * @param   string   $path      The path to the current nodes.
	 *
	 * @return  integer  1 + value of root rgt on success, false on failure
	 *
	 * @since   11.1
	 * @throws  \RuntimeException on database error.
	 */
	public function rebuild($parentId = null, $leftId = 0, $level = 0, $path = '')
	{
		// If no parent is provided, try to find it.
		if ($parentId === null)
		{
			// Get the root item.
			$parentId = $this->getRootId();

			if ($parentId === false)
			{
				return false;
			}
		}

		$query = $this->db->getQuery(true);

		// Build the structure of the recursive query.
		if (!isset($this->cache['rebuild.sql']))
		{
			$query->clear()
				->select($this->getKeyName() . ', alias')
				->from($this->table)
				->where('parent_id = "%s"')
				->order('parent_id, lft');

			$this->cache['rebuild.sql'] = (string) $query;
		}

		// Make a shortcut to database object.

		// Assemble the query to find all children of this node.
		$children = $this->db->getReader(sprintf($this->cache['rebuild.sql'], $parentId))->loadObjectList();

		// The right value of this node is the left value + 1
		$rightId = $leftId + 1;

		// Execute this function recursively over all children
		foreach ($children as $node)
		{
			/*
			 * $rightId is the current right value, which is incremented on recursion return.
			 * Increment the level for the children.
			 * Add this item's alias to the path (but avoid a leading /)
			 */
			$rightId = $this->rebuild($node->{$this->getKeyName()}, $rightId, $level + 1, $path . (empty($path) ? '' : '/') . $node->alias);

			// If there is an update failure, return false to break out of the recursion.
			if ($rightId === false)
			{
				return false;
			}
		}

		// We've got the left value, and now that we've processed
		// the children of this node we also know the right value.
		$query->clear()
			->update($this->table)
			->set('lft = ' . (int) $leftId)
			->set('rgt = ' . (int) $rightId)
			->set('level = ' . (int) $level)
			->set('path = ' . $this->db->quote($path))
			->where($this->getKeyName() . ' = ' . (int) $parentId);

		$this->db->setQuery($query)->execute();

		// Return the right value of this node + 1.
		return $rightId + 1;
	}

	/**
	 * Method to rebuild the node's path field from the alias values of the
	 * nodes from the current node to the root node of the tree.
	 *
	 * @param   integer  $pk  Primary key of the node for which to get the path.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function rebuildPath($pk = null)
	{
		$fields = $this->getFields();

		// If there is no alias or path field, just return true.
		if (!array_key_exists('alias', $fields) || !array_key_exists('path', $fields))
		{
			return true;
		}

		$k = $this->getKeyName();
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the aliases for the path from the node to the root node.
		$query = $this->db->getQuery(true)
			->select('p.alias')
			->from($this->table . ' AS n, ' . $this->table . ' AS p')
			->where('n.lft BETWEEN p.lft AND p.rgt')
			->where('n.' . $this->getKeyName() . ' = ' . (int) $pk)
			->order('p.lft');

		$this->db->setQuery($query);

		$segments = $this->db->loadColumn();

		// Make sure to remove the root path if it exists in the list.
		if ($segments[0] == 'root')
		{
			array_shift($segments);
		}

		// Build the path.
		$path = trim(implode('/', $segments), ' /\\');

		// Update the path field for the node.
		$query->clear()
			->update($this->table)
			->set('path = ' . $this->db->quote($path))
			->where($this->getKeyName() . ' = ' . (int) $pk);

		$this->db->setQuery($query)->execute();

		// Update the current record's path to the new one:
		$this->path = $path;

		return true;
	}

	/**
	 * createRoot
	 *
	 * @return  boolean
	 */
	public function createRoot()
	{
		$record = new Record($this->table);

		$key = $this->getKeyName();

		$record->parent_id = 0;
		$record->lft = 0;
		$record->rgt = 1;
		$record->level = 0;
		$record->title = 'root';
		$record->alias = 'root';
		$record->access = 1;

		static::$rootId = $record->$key;

		return $record->store();
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties (except $_errors).
	 *
	 * @param bool $clear
	 *
	 * @return  static
	 *
	 * @since   3.2.1
	 */
	public function reset($clear = false)
	{
		parent::reset();

		// Reset the location properties.
		$this->setLocation(0);

		return $this;
	}

	/**
	 * Method to get nested set properties for a node in the tree.
	 *
	 * @param   integer  $id   Value to look up the node by.
	 * @param   string   $key  An optional key to look up the node by (parent | left | right).
	 *                         If omitted, the primary key of the table is used.
	 *
	 * @return  mixed    Boolean false on failure or node object on success.
	 *
	 * @since   11.1
	 * @throws  \RuntimeException on database error.
	 */
	protected function getNode($id, $key = null)
	{
		// Determine which key to get the node base on.
		switch ($key)
		{
			case 'parent':
				$k = 'parent_id';
				break;

			case 'left':
				$k = 'lft';
				break;

			case 'right':
				$k = 'rgt';
				break;

			default:
				$k = $this->getKeyName();
				break;
		}

		// Get the node data.
		$query = $this->db->getQuery(true)
			->select($this->getKeyName() . ', parent_id, level, lft, rgt')
			->from($this->table)
			->where($k . ' = ' . $id)
			->limit(1);

		$row = $this->db->getReader($query)->loadObject();

		// Check for no $row returned
		if (empty($row))
		{
			throw new \UnexpectedValueException(sprintf('%s::getNode(%d, %s) failed.', get_class($this), $id, $key));
		}

		// Do some simple calculations.
		$row->numChildren = (int) ($row->rgt - $row->lft - 1) / 2;
		$row->width = (int) $row->rgt - $row->lft + 1;

		return $row;
	}

	/**
	 * Method to get various data necessary to make room in the tree at a location
	 * for a node and its children.  The returned data object includes conditions
	 * for SQL WHERE clauses for updating left and right id values to make room for
	 * the node as well as the new left and right ids for the node.
	 *
	 * @param   object   $referenceNode  A node object with at least a 'lft' and 'rgt' with
	 *                                   which to make room in the tree around for a new node.
	 * @param   integer  $nodeWidth      The width of the node for which to make room in the tree.
	 * @param   integer  $position       The position relative to the reference node where the room
	 *                                   should be made.
	 *
	 * @return  mixed    Boolean false on failure or data object on success.
	 *
	 * @since   11.1
	 */
	protected function getTreeRepositionData($referenceNode, $nodeWidth, $position = self::LOCATION_BEFORE)
	{
		// Make sure the reference an object with a left and right id.
		if (!is_object($referenceNode) || !(isset($referenceNode->lft) && isset($referenceNode->rgt)))
		{
			throw new \InvalidArgumentException('Left and right id not exists');
		}

		// A valid node cannot have a width less than 2.
		if ($nodeWidth < 2)
		{
			return false;
		}

		$k = $this->getKeyName();
		$data = new \stdClass;

		// Run the calculations and build the data object by reference position.
		switch ($position)
		{
			case static::LOCATION_FIRST_CHILD:
				$data->left_where = 'lft > ' . $referenceNode->lft;
				$data->right_where = 'rgt >= ' . $referenceNode->lft;

				$data->new_lft = $referenceNode->lft + 1;
				$data->new_rgt = $referenceNode->lft + $nodeWidth;
				$data->new_parent_id = $referenceNode->$k;
				$data->new_level = $referenceNode->level + 1;
				break;

			case static::LOCATION_LAST_CHILD:
				$data->left_where = 'lft > ' . ($referenceNode->rgt);
				$data->right_where = 'rgt >= ' . ($referenceNode->rgt);

				$data->new_lft = $referenceNode->rgt;
				$data->new_rgt = $referenceNode->rgt + $nodeWidth - 1;
				$data->new_parent_id = $referenceNode->$k;
				$data->new_level = $referenceNode->level + 1;
				break;

			case static::LOCATION_BEFORE;
				$data->left_where = 'lft >= ' . $referenceNode->lft;
				$data->right_where = 'rgt >= ' . $referenceNode->lft;

				$data->new_lft = $referenceNode->lft;
				$data->new_rgt = $referenceNode->lft + $nodeWidth - 1;
				$data->new_parent_id = $referenceNode->parent_id;
				$data->new_level = $referenceNode->level;
				break;

			default:
			case static::LOCATION_AFTER:
				$data->left_where = 'lft > ' . $referenceNode->rgt;
				$data->right_where = 'rgt > ' . $referenceNode->rgt;

				$data->new_lft = $referenceNode->rgt + 1;
				$data->new_rgt = $referenceNode->rgt + $nodeWidth;
				$data->new_parent_id = $referenceNode->parent_id;
				$data->new_level = $referenceNode->level;
				break;
		}

		return $data;
	}
}
