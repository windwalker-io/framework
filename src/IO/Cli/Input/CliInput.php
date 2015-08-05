<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\IO\Cli\Input;

use Windwalker\IO\Filter\NullFilter;
use Windwalker\IO\Input;

/**
 * Windwalker Input CLI Class
 *
 * @since  2.0
 */
class CliInput extends Input implements CliInputInterface
{
	/**
	 * The executable that was called to run the CLI script.
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $calledScript;

	/**
	 * The additional arguments passed to the script that are not associated
	 * with a specific argument name.
	 *
	 * @var    array
	 * @since  2.0
	 */
	public $args = array();

	/**
	 * Property inputStream.
	 *
	 * @var  resource
	 */
	protected $inputStream = STDIN;

	/**
	 * Prepare source.
	 *
	 * @param   array    $source     Optional source data. If omitted, a copy of the server variable '_REQUEST' is used.
	 * @param   boolean  $reference  If set to true, he source in first argument will be reference.
	 *
	 * @return  void
	 */
	public function prepareSource(&$source = null, $reference = false)
	{
		// Get the command line options
		$this->parseArguments();
	}

	/**
	 * Method to serialize the input.
	 *
	 * @return  string  The serialized input.
	 *
	 * @since   2.0
	 */
	public function serialize()
	{
		// Load all of the inputs.
		$this->loadAllInputs();

		// Remove $_ENV and $_SERVER from the inputs.
		$inputs = $this->inputs;
		unset($inputs['env']);
		unset($inputs['server']);

		// Serialize the executable, args, options, data, and inputs.
		return serialize(array($this->calledScript, $this->args, $this->filter, $this->data, $inputs));
	}

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 * @param   string  $filter   Filter to apply to the value.
	 *
	 * @return  mixed  The filtered input value.
	 *
	 * @since   2.0
	 */
	public function get($name, $default = null, $filter = 'string')
	{
		return parent::get($name, $default, $filter);
	}

	/**
	 * Gets an array of values from the request.
	 *
	 * @return  mixed  The filtered input data.
	 *
	 * @since   2.0
	 */
	public function all()
	{
		return $this->getArray();
	}

	/**
	 * Method to unserialize the input.
	 *
	 * @param   string  $input  The serialized input.
	 *
	 * @return  Input  The input object.
	 *
	 * @since   2.0
	 */
	public function unserialize($input)
	{
		// Unserialize the executable, args, options, data, and inputs.
		list($this->calledScript, $this->args, $this->filter, $this->data, $this->inputs) = unserialize($input);

		$this->filter = $this->filter ? : new NullFilter;
	}

	/**
	 * getArgument
	 *
	 * @param integer $offset
	 * @param mixed   $default
	 *
	 * @return  mixed
	 */
	public function getArgument($offset, $default = null)
	{
		return isset($this->args[$offset]) ? $this->args[$offset] : $default;
	}

	/**
	 * setArgument
	 *
	 * @param integer $offset
	 * @param mixed   $value
	 *
	 * @return  CliInput
	 */
	public function setArgument($offset, $value)
	{
		$this->args[$offset] = $value;

		return $this;
	}

	/**
	 * Initialise the options and arguments
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected function parseArguments()
	{
		$argv = $_SERVER['argv'];

		$this->calledScript = array_shift($argv);

		$out = array();

		for ($i = 0, $j = count($argv); $i < $j; $i++)
		{
			$arg = $argv[$i];

			// --foo --bar=baz
			if (substr($arg, 0, 2) === '--')
			{
				$eqPos = strpos($arg, '=');

				// --foo
				if ($eqPos === false)
				{
					$key = substr($arg, 2);

					// --foo value
					if ($i + 1 < $j && $argv[$i + 1][0] !== '-')
					{
						$value = $argv[$i + 1];
						$i++;
					}
					else
					{
						$value = isset($out[$key]) ? $out[$key] : true;
					}

					$out[$key] = $value;
				}

				// --bar=baz
				else
				{
					$key       = substr($arg, 2, $eqPos - 2);
					$value     = substr($arg, $eqPos + 1);
					$out[$key] = $value;
				}
			}

			// -k=value -abc
			else
			{
				if (substr($arg, 0, 1) === '-')
				{
					// -k=value
					if (substr($arg, 2, 1) === '=')
					{
						$key       = substr($arg, 1, 1);
						$value     = substr($arg, 3);
						$out[$key] = $value;
					}
					// -abc
					else
					{
						$chars = str_split(substr($arg, 1));

						foreach ($chars as $char)
						{
							$key       = $char;
							$value     = isset($out[$key]) ? $out[$key] : true;
							$out[$key] = $value;
						}

						// -a a-value
						if ((count($chars) === 1) && ($i + 1 < $j) && ($argv[$i + 1][0] !== '-'))
						{
							$out[$key] = $argv[$i + 1];
							$i++;
						}
					}
				}

				// plain-arg
				else
				{
					$this->args[] = $arg;
				}
			}
		}

		$this->data = $out;
	}

	/**
	 * Get a value from standard input.
	 *
	 * @return  string  The input string from standard input.
	 */
	public function in()
	{
		return rtrim(fread($this->inputStream, 8192), "\n\r");
	}

	/**
	 * getInputStream
	 *
	 * @return  resource
	 */
	public function getInputStream()
	{
		return $this->inputStream;
	}

	/**
	 * setInputStream
	 *
	 * @param   resource $inputStream
	 *
	 * @return  CliInput  Return self to support chaining.
	 */
	public function setInputStream($inputStream)
	{
		$this->inputStream = $inputStream;

		return $this;
	}

	/**
	 * getCalledScript
	 *
	 * @return  string
	 */
	public function getCalledScript()
	{
		return $this->calledScript;
	}

	/**
	 * setCalledScript
	 *
	 * @param   string $calledScript
	 *
	 * @return  CliInput  Return self to support chaining.
	 */
	public function setCalledScript($calledScript)
	{
		$this->calledScript = $calledScript;

		return $this;
	}
}
