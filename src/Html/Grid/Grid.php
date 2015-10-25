<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2015 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Html\Grid;

/**
 * The Grid class to dynamically generate HTML tables.
 *
 * This is currently a Joomla JGrid fork.
 *
 * @see  https://github.com/joomla/joomla-cms/blob/staging/libraries/joomla/grid/grid.php
 * 
 * @since  2.1
 */
class Grid
{
	const ROW_HEAD   = 1;
	const ROW_FOOT   = 2;
	const ROW_NORMAL = 3;

	/**
	 * Array of columns
	 * @var array
	 */
	protected $columns = array();

	/**
	 * Current active row
	 * @var int
	 */
	protected $activeRow = 0;

	/**
	 * Rows of the table (including header and footer rows)
	 * @var array
	 */
	protected $rows = array();

	/**
	 * Header and Footer row-IDs
	 * @var array
	 */
	protected $specialRows = array('header' => array(), 'footer' => array());

	/**
	 * Associative array of attributes for the table-tag
	 * @var array
	 */
	protected $options;

	/**
	 * Constructor for a Grid object
	 *
	 * @param   array  $options  Associative array of attributes for the table-tag
	 *
	 * @since   2.1
	 */
	public function __construct($options = array())
	{
		$this->setTableOptions($options, true);
	}

	/**
	 * create
	 *
	 * @param array $options
	 *
	 * @return static
	 */
	public static function create($options = array())
	{
		return new static($options);
	}

	/**
	 * Magic function to render this object as a table.
	 *
	 * @return  string
	 *
	 * @since 2.1
	 */
	public function __toString()
	{
		try
		{
			return $this->toString();
		}
		catch (\Exception $e)
		{
			return (string) $e;
		}
	}

	/**
	 * Method to set the attributes for a table-tag
	 *
	 * @param   array  $options  Associative array of attributes for the table-tag
	 * @param   bool   $replace  Replace possibly existing attributes
	 *
	 * @return  static This object for chaining
	 *
	 * @since 2.1
	 */
	public function setTableOptions($options = array(), $replace = false)
	{
		if ($replace)
		{
			$this->options = $options;
		}
		else
		{
			$this->options = array_merge($this->options, $options);
		}

		return $this;
	}

	/**
	 * Get the Attributes of the current table
	 *
	 * @return  array Associative array of attributes
	 *
	 * @since 2.1
	 */
	public function getTableOptions()
	{
		return $this->options;
	}

	/**
	 * Add new column name to process
	 *
	 * @param   string  $name  Internal column name
	 *
	 * @return  static This object for chaining
	 *
	 * @since 2.1
	 */
	public function addColumn($name)
	{
		$this->columns[] = $name;

		return $this;
	}

	/**
	 * Returns the list of internal columns
	 *
	 * @return  array List of internal columns
	 *
	 * @since 2.1
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * Delete column by name
	 *
	 * @param   string  $name  Name of the column to be deleted
	 *
	 * @return  static This object for chaining
	 *
	 * @since 2.1
	 */
	public function deleteColumn($name)
	{
		$index = array_search($name, $this->columns);

		if ($index !== false)
		{
			unset($this->columns[$index]);
			$this->columns = array_values($this->columns);
		}

		return $this;
	}

	/**
	 * Method to set a whole range of columns at once
	 * This can be used to re-order the columns, too
	 *
	 * @param   array  $columns  List of internal column names
	 *
	 * @return  static This object for chaining
	 *
	 * @since 2.1
	 */
	public function setColumns($columns)
	{
		$this->columns = array_values($columns);

		return $this;
	}

	/**
	 * Adds a row to the table and sets the currently
	 * active row to the new row
	 *
	 * @param   array     $options  Associative array of attributes for the row
	 * @param   int|bool  $special  1 for a new row in the header, 2 for a new row in the footer
	 *
	 * @return  static This object for chaining
	 *
	 * @since   2.1
	 */
	public function addRow($options = array(), $special = self::ROW_NORMAL)
	{
		$this->rows[]['_row'] = $options;
		$this->activeRow = count($this->rows) - 1;

		if ($special)
		{
			if ($special === static::ROW_HEAD)
			{
				$this->specialRows['header'][] = $this->activeRow;
			}
			elseif ($special === static::ROW_FOOT)
			{
				$this->specialRows['footer'][] = $this->activeRow;
			}
		}

		return $this;
	}

	/**
	 * Method to get the attributes of the currently active row
	 *
	 * @return array Associative array of attributes
	 *
	 * @since   2.1
	 */
	public function getRowOptions()
	{
		return $this->rows[$this->activeRow]['_row'];
	}

	/**
	 * Method to set the attributes of the currently active row
	 *
	 * @param   array  $options  Associative array of attributes
	 *
	 * @return  static This object for chaining
	 *
	 * @since   2.1
	 */
	public function setRowOptions($options)
	{
		$this->rows[$this->activeRow]['_row'] = $options;

		return $this;
	}

	/**
	 * Get the currently active row ID
	 *
	 * @return  int ID of the currently active row
	 *
	 * @since   2.1
	 */
	public function getActiveRow()
	{
		return $this->activeRow;
	}

	/**
	 * Set the currently active row
	 *
	 * @param   int  $id  ID of the row to be set to current
	 *
	 * @return  static This object for chaining
	 *
	 * @since  2.1
	 */
	public function setActiveRow($id)
	{
		$this->activeRow = (int) $id;

		return $this;
	}

	/**
	 * Set cell content for a specific column for the
	 * currently active row
	 *
	 * @param   string  $name     Name of the column
	 * @param   string  $content  Content for the cell
	 * @param   array   $option   Associative array of attributes for the td-element
	 * @param   bool    $replace  If false, the content is appended to the current content of the cell
	 *
	 * @return  static This object for chaining
	 *
	 * @since 2.1
	 */
	public function setRowCell($name, $content, $option = array(), $replace = true)
	{
		if ($replace || !isset($this->rows[$this->activeRow][$name]))
		{
			$cell = new \stdClass;
			$cell->options = $option;
			$cell->content = $content;
			$this->rows[$this->activeRow][$name] = $cell;
		}
		else
		{
			$this->rows[$this->activeRow][$name]->content .= $content;
			$this->rows[$this->activeRow][$name]->options = $option;
		}

		return $this;
	}

	/**
	 * Get all data for a row
	 *
	 * @param   int|bool  $id  ID of the row to return
	 *
	 * @return  array Array of columns of a table row
	 *
	 * @since   2.1
	 */
	public function getRow($id = false)
	{
		if ($id === false)
		{
			$id = $this->activeRow;
		}

		if (isset($this->rows[(int) $id]))
		{
			return $this->rows[(int) $id];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get the IDs of all rows in the table
	 *
	 * @param   int|bool  $special  false for the standard rows, 1 for the header rows, 2 for the footer rows
	 *
	 * @return  array Array of IDs
	 *
	 * @since 2.1
	 */
	public function getRows($special = false)
	{
		if ($special)
		{
			if ($special === 1)
			{
				return $this->specialRows['header'];
			}
			else
			{
				return $this->specialRows['footer'];
			}
		}

		return array_diff(array_keys($this->rows), array_merge($this->specialRows['header'], $this->specialRows['footer']));
	}

	/**
	 * Delete a row from the object
	 *
	 * @param   int  $id  ID of the row to be deleted
	 *
	 * @return  static This object for chaining
	 *
	 * @since   2.1
	 */
	public function deleteRow($id)
	{
		unset($this->rows[$id]);

		if (in_array($id, $this->specialRows['header']))
		{
			unset($this->specialRows['header'][array_search($id, $this->specialRows['header'])]);
		}

		if (in_array($id, $this->specialRows['footer']))
		{
			unset($this->specialRows['footer'][array_search($id, $this->specialRows['footer'])]);
		}

		if ($this->activeRow == $id)
		{
			end($this->rows);
			$this->activeRow = key($this->rows);
		}

		return $this;
	}

	/**
	 * Render the HTML table
	 *
	 * @return  string The rendered HTML table
	 *
	 * @since   2.1
	 */
	public function toString()
	{
		$output = array();
		$output[] = '<table' . $this->renderAttributes($this->getTableOptions()) . '>';

		if (count($this->specialRows['header']))
		{
			$output[] = $this->renderArea($this->specialRows['header'], 'thead', 'th');
		}

		if (count($this->specialRows['footer']))
		{
			$output[] = $this->renderArea($this->specialRows['footer'], 'tfoot');
		}

		$ids = array_diff(array_keys($this->rows), array_merge($this->specialRows['header'], $this->specialRows['footer']));

		if (count($ids))
		{
			$output[] = $this->renderArea($ids);
		}

		$output[] = '</table>';

		return implode('', $output);
	}

	/**
	 * Render an area of the table
	 *
	 * @param   array   $ids   IDs of the rows to render
	 * @param   string  $area  Name of the area to render. Valid: tbody, tfoot, thead
	 * @param   string  $cell  Name of the cell to render. Valid: td, th
	 *
	 * @return string The rendered table area
	 *
	 * @since 2.1
	 */
	protected function renderArea($ids, $area = 'tbody', $cell = 'td')
	{
		$output = array();
		$output[] = '<' . $area . ">\n";

		foreach ($ids as $id)
		{
			$output[] = "\t<tr" . $this->renderAttributes($this->rows[$id]['_row']) . ">\n";

			foreach ($this->getColumns() as $name)
			{
				if (isset($this->rows[$id][$name]))
				{
					$column = $this->rows[$id][$name];
					$output[] = "\t\t<" . $cell . $this->renderAttributes($column->options) . '>' . $column->content . '</' . $cell . ">\n";
				}
			}

			$output[] = "\t</tr>\n";
		}

		$output[] = '</' . $area . '>';

		return implode('', $output);
	}

	/**
	 * Renders an HTML attribute from an associative array
	 *
	 * @param   array  $attributes  Associative array of attributes
	 *
	 * @return  string The HTML attribute string
	 *
	 * @since   2.1
	 */
	protected function renderAttributes($attributes)
	{
		if (count((array) $attributes) == 0)
		{
			return '';
		}

		$return = array();

		foreach ($attributes as $key => $option)
		{
			$return[] = $key . '="' . $option . '"';
		}

		return ' ' . implode(' ', $return);
	}
}
