<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Utilities\Test;

use Windwalker\Test\TestCase\AbstractBaseTestCase;
use Windwalker\Utilities\ArrayHelper;

/**
 * Test class of ArrayHelper
 *
 * @since 2.0
 */
class ArrayHelperTest extends AbstractBaseTestCase
{
	/**
	 * Data provider for testArrayUnique.
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestArrayUnique()
	{
		return [
			'Case 1' => [
				// Input
				[
					[1, 2, 3, [4]],
					[2, 2, 3, [4]],
					[3, 2, 3, [4]],
					[2, 2, 3, [4]],
					[3, 2, 3, [4]],
				],
				// Expected
				[
					[1, 2, 3, [4]],
					[2, 2, 3, [4]],
					[3, 2, 3, [4]],
				],
			]
		];
	}

	/**
	 * Data provider for get column
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestGetColumn()
	{
		return [
			'generic array' => [
				[
					[
						1, 2, 3, 4, 5
					], [
					6, 7, 8, 9, 10
				], [
					11, 12, 13, 14, 15
				], [
					16, 17, 18, 19, 20
				]
				],
				2,
				[
					3, 8, 13, 18
				],
				'Should get column #2'
			],
			'associative array' => [
				[
					[
						'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5
					],
					[
						'one' => 6, 'two' => 7, 'three' => 8, 'four' => 9, 'five' => 10
					],
					[
						'one' => 11, 'two' => 12, 'three' => 13, 'four' => 14, 'five' => 15
					],
					[
						'one' => 16, 'two' => 17, 'three' => 18, 'four' => 19, 'five' => 20
					]
				],
				'four',
				[
					4, 9, 14, 19
				],
				'Should get column \'four\''
			],
			'object array' => [
				[
					(object) [
						'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5
					],
					(object) [
						'one' => 6, 'two' => 7, 'three' => 8, 'four' => 9, 'five' => 10
					],
					(object) [
						'one' => 11, 'two' => 12, 'three' => 13, 'four' => 14, 'five' => 15
					],
					(object) [
						'one' => 16, 'two' => 17, 'three' => 18, 'four' => 19, 'five' => 20
					]
				],
				'four',
				[
					4, 9, 14, 19
				],
				'Should get column \'four\''
			],
		];
	}

	/**
	 * Data provider for get value
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestGetValue()
	{
		$input = [
			'one' => 1,
			'two' => 2,
			'three' => 3,
			'four' => 4,
			'five' => 5,
			'six' => 6,
			'seven' => 7,
			'eight' => 8,
			'nine' => 'It\'s nine',
			'ten' => 10,
			'eleven' => 11,
			'twelve' => 12,
			'thirteen' => 13,
			'fourteen' => 14,
			'fifteen' => 15,
			'sixteen' => 16,
			'seventeen' => 17,
			'eightteen' => 'eighteen ninety-five',
			'nineteen' => 19,
			'twenty' => 20
		];

		return [
			'defaults' => [
				$input, 'five', null, null, 5, 'Should get 5', true
			],
			'get non-value' => [
				$input, 'fiveio', 198, null, 198, 'Should get the default value', false
			],
			'get int 5' => [
				$input, 'five', 198, 'int', (int) 5, 'Should get an int', false
			],
			'get float six' => [
				$input, 'six', 198, 'float', (float) 6, 'Should get a float', false
			],
			'get get boolean seven' => [
				$input, 'seven', 198, 'bool', (bool) 7, 'Should get a boolean', false
			],
			'get array eight' => [
				$input, 'eight', 198, 'array', [
					8
				], 'Should get an array', false
			],
			'get string nine' => [
				$input, 'nine', 198, 'string', 'It\'s nine', 'Should get string', false
			],
			'get word' => [
				$input, 'eightteen', 198, 'word', 'eighteenninetyfive', 'Should get it as a single word', false
			],
		];
	}

	/**
	 * Data provider for invert
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestInvert()
	{
		return [
			'Case 1' => [
				// Input
				[
					'New' => ['1000', '1500', '1750'],
					'Used' => ['3000', '4000', '5000', '6000']
				],
				// Expected
				[
					'1000' => 'New',
					'1500' => 'New',
					'1750' => 'New',
					'3000' => 'Used',
					'4000' => 'Used',
					'5000' => 'Used',
					'6000' => 'Used'
				]
			],
			'Case 2' => [
				// Input
				[
					'New' => [1000, 1500, 1750],
					'Used' => [2750, 3000, 4000, 5000, 6000],
					'Refurbished' => [2000, 2500],
					'Unspecified' => []
				],
				// Expected
				[
					'1000' => 'New',
					'1500' => 'New',
					'1750' => 'New',
					'2750' => 'Used',
					'3000' => 'Used',
					'4000' => 'Used',
					'5000' => 'Used',
					'6000' => 'Used',
					'2000' => 'Refurbished',
					'2500' => 'Refurbished'
				]
			],
			'Case 3' => [
				// Input
				[
					'New' => [1000, 1500, 1750],
					'valueNotAnArray' => 2750,
					'withNonScalarValue' => [2000, [1000 , 3000]]
				],
				// Expected
				[
					'1000' => 'New',
					'1500' => 'New',
					'1750' => 'New',
					'2000' => 'withNonScalarValue'
				]
			]
		];
	}

	/**
	 * Data provider for testGroup
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestGroup()
	{
		return [
			'A scalar array' => [
				// Source
				[
					1 => 'a',
					2 => 'b',
					3 => 'b',
					4 => 'c',
					5 => 'a',
					6 => 'a',
				],
				// Key
				null,
				// Expected
				[
					'a' => [
						1, 5, 6
					],
					'b' => [
						2, 3
					],
					'c' => 4,
				]
			],
			'An array of associative arrays' => [
				// Source
				[
					1 => ['id' => 41, 'title' => 'boo'],
					2 => ['id' => 42, 'title' => 'boo'],
					3 => ['title' => 'boo'],
					4 => ['id' => 42, 'title' => 'boo'],
					5 => ['id' => 43, 'title' => 'boo'],
				],
				// Key
				'id',
				// Expected
				[
					41 => ['id' => 41, 'title' => 'boo'],
					42 => [
						['id' => 42, 'title' => 'boo'],
						['id' => 42, 'title' => 'boo'],
					],
					43 => ['id' => 43, 'title' => 'boo'],
				]
			],
			'An array of objects' => [
				// Source
				[
					1 => (object) ['id' => 41, 'title' => 'boo'],
					2 => (object) ['id' => 42, 'title' => 'boo'],
					3 => (object) ['title' => 'boo'],
					4 => (object) ['id' => 42, 'title' => 'boo'],
					5 => (object) ['id' => 43, 'title' => 'boo'],
				],
				// Key
				'id',
				// Expected
				[
					41 => (object) ['id' => 41, 'title' => 'boo'],
					42 => [
						(object) ['id' => 42, 'title' => 'boo'],
						(object) ['id' => 42, 'title' => 'boo'],
					],
					43 => (object) ['id' => 43, 'title' => 'boo'],
				]
			],
		];
	}

	/**
	 * seedTestTranspose
	 *
	 * @return array
	 */
	public function seedTestPivot()
	{
		return [
			[
				// data
				[
					'Jones'  => [123, 223],
					'Arthur' => ['Lancelot', 'Jessica']
				],
				// expected
				[
					['Jones' => 123, 'Arthur' => 'Lancelot'],
					['Jones' => 223, 'Arthur' => 'Jessica'],
				],
			],
		];
	}

	/**
	 * Data provider for sorting objects
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestSortObject()
	{
		$input1 = [
			(object) [
				'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
			],
			(object) [
				'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
			],
			(object) [
				'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
			],
			(object) [
				'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
			],
			(object) [
				'integer' => 5, 'float' => 1.29999, 'string' => 'T Test String'
			],
			(object) [
				'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
			],
			(object) [
				'integer' => 6, 'float' => 1.29999, 'string' => 'G Test String'
			],
			(object) [
				'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
			],
		];
		$input2 = [
			(object) [
				'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
			],
			(object) [
				'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
			],
			(object) [
				'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
			],
			(object) [
				'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
			],
			(object) [
				'integer' => 5, 'float' => 1.29999, 'string' => 't Test String'
			],
			(object) [
				'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
			],
			(object) [
				'integer' => 6, 'float' => 1.29999, 'string' => 'g Test String'
			],
			(object) [
				'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
			],
		];

		if (substr(php_uname(), 0, 6) != 'Darwin')
		{
			$input3 = [
				(object) [
					'string' => 'A Test String', 'integer' => 1,
				],
				(object) [
					'string' => 'é Test String', 'integer' => 2,
				],
				(object) [
					'string' => 'è Test String', 'integer' => 3,
				],
				(object) [
					'string' => 'É Test String', 'integer' => 4,
				],
				(object) [
					'string' => 'È Test String', 'integer' => 5,
				],
				(object) [
					'string' => 'Œ Test String', 'integer' => 6,
				],
				(object) [
					'string' => 'œ Test String', 'integer' => 7,
				],
				(object) [
					'string' => 'L Test String', 'integer' => 8,
				],
				(object) [
					'string' => 'P Test String', 'integer' => 9,
				],
				(object) [
					'string' => 'p Test String', 'integer' => 10,
				],
			];
		}
		else
		{
			$input3 = [];
		}

		return [
			'by int defaults' => [
				$input1,
				'integer',
				null,
				false,
				false,
				[
					(object) [
						'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
					],
					(object) [
						'integer' => 5, 'float' => 1.29999, 'string' => 'T Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'G Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
					],
					(object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					(object) [
						'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
					],
					(object) [
						'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
					],
				],
				'Should be sorted by the integer field in ascending order',
				true
			],
			'by int ascending' => [
				$input1,
				'integer',
				1,
				false,
				false,
				[
					(object) [
						'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
					],
					(object) [
						'integer' => 5, 'float' => 1.29999, 'string' => 'T Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'G Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
					],
					(object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					(object) [
						'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
					],
					(object) [
						'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
					],
				],
				'Should be sorted by the integer field in ascending order full argument list',
				false
			],
			'by int descending' => [
				$input1,
				'integer',
				-1,
				false,
				false,
				[
					(object) [
						'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
					],
					(object) [
						'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'G Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
					],
					(object) [
						'integer' => 5, 'float' => 1.29999, 'string' => 'T Test String'
					],
					(object) [
						'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
					],
				],
				'Should be sorted by the integer field in descending order',
				false
			],
			'by string ascending' => [
				$input1,
				'string',
				1,
				false,
				false,
				[
					(object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					(object) [
						'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'G Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
					],
					(object) [
						'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
					],
					(object) [
						'integer' => 5, 'float' => 1.29999, 'string' => 'T Test String'
					],
				],
				'Should be sorted by the string field in ascending order full argument list',
				false,
				[1, 2]
			],
			'by string descending' => [
				$input1,
				'string',
				-1,
				false,
				false,
				[
					(object) [
						'integer' => 5, 'float' => 1.29999, 'string' => 'T Test String'
					],
					(object) [
						'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'G Test String'
					],
					(object) [
						'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
					],
					(object) [
						'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
				],
				'Should be sorted by the string field in descending order',
				false,
				[5, 6]
			],
			'by casesensitive string ascending' => [
				$input2,
				'string',
				1,
				true,
				false,
				[
					(object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					(object) [
						'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
					],
					(object) [
						'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'g Test String'
					],
					(object) [
						'integer' => 5, 'float' => 1.29999, 'string' => 't Test String'
					],
				],
				'Should be sorted by the string field in ascending order with casesensitive comparisons',
				false,
				[1, 2]
			],
			'by casesensitive string descending' => [
				$input2,
				'string',
				-1,
				true,
				false,
				[
					(object) [
						'integer' => 5, 'float' => 1.29999, 'string' => 't Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'g Test String'
					],
					(object) [
						'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
					],
					(object) [
						'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
					],
					(object) [
						'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
				],
				'Should be sorted by the string field in descending order with casesensitive comparisons',
				false,
				[5, 6]
			],
			'by casesensitive string,integer ascending' => [
				$input2,
				[
					'string', 'integer'
				],
				1,
				true,
				false,
				[
					(object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					(object) [
						'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
					],
					(object) [
						'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'g Test String'
					],
					(object) [
						'integer' => 5, 'float' => 1.29999, 'string' => 't Test String'
					],
				],
				'Should be sorted by the string,integer field in descending order with casesensitive comparisons',
				false
			],
			'by casesensitive string,integer descending' => [
				$input2,
				[
					'string', 'integer'
				],
				-1,
				true,
				false,
				[
					(object) [
						'integer' => 5, 'float' => 1.29999, 'string' => 't Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'g Test String'
					],
					(object) [
						'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
					],
					(object) [
						'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
					],
					(object) [
						'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
				],
				'Should be sorted by the string,integer field in descending order with casesensitive comparisons',
				false
			],
			'by casesensitive string,integer ascending,descending' => [
				$input2,
				[
					'string', 'integer'
				],
				[
					1, -1
				],
				true,
				false,
				[
					(object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					(object) [
						'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
					],
					(object) [
						'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'g Test String'
					],
					(object) [
						'integer' => 5, 'float' => 1.29999, 'string' => 't Test String'
					],
				],
				'Should be sorted by the string,integer field in ascending,descending order with casesensitive comparisons',
				false
			],
			'by casesensitive string,integer descending,ascending' => [
				$input2,
				[
					'string', 'integer'
				],
				[
					-1, 1
				],
				true,
				false,
				[
					(object) [
						'integer' => 5, 'float' => 1.29999, 'string' => 't Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'g Test String'
					],
					(object) [
						'integer' => 1, 'float' => 1.29999, 'string' => 'N Test String'
					],
					(object) [
						'integer' => 6, 'float' => 1.29999, 'string' => 'L Test String'
					],
					(object) [
						'integer' => 22, 'float' => 1.29999, 'string' => 'E Test String'
					],
					(object) [
						'integer' => 15, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 35, 'float' => 1.29999, 'string' => 'C Test String'
					],
					(object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
				],
				'Should be sorted by the string,integer field in descending,ascending order with casesensitive comparisons',
				false
			],
			'by casesensitive string ascending' => [
				$input3,
				'string',
				1,
				true,
				[
					'fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'
				],
				[
					(object) [
						'string' => 'A Test String', 'integer' => 1,
					],
					(object) [
						'string' => 'é Test String', 'integer' => 2,
					],
					(object) [
						'string' => 'É Test String', 'integer' => 4,
					],
					(object) [
						'string' => 'è Test String', 'integer' => 3,
					],
					(object) [
						'string' => 'È Test String', 'integer' => 5,
					],
					(object) [
						'string' => 'L Test String', 'integer' => 8,
					],
					(object) [
						'string' => 'œ Test String', 'integer' => 7,
					],
					(object) [
						'string' => 'Œ Test String', 'integer' => 6,
					],
					(object) [
						'string' => 'p Test String', 'integer' => 10,
					],
					(object) [
						'string' => 'P Test String', 'integer' => 9,
					],
				],
				'Should be sorted by the string field in ascending order with casesensitive comparisons and fr_FR locale',
				false
			],
			'by caseinsensitive string, integer ascending' => [
				$input3,
				[
					'string', 'integer'
				],
				1,
				false,
				[
					'fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'
				],
				[
					(object) [
						'string' => 'A Test String', 'integer' => 1,
					],
					(object) [
						'string' => 'é Test String', 'integer' => 2,
					],
					(object) [
						'string' => 'É Test String', 'integer' => 4,
					],
					(object) [
						'string' => 'è Test String', 'integer' => 3,
					],
					(object) [
						'string' => 'È Test String', 'integer' => 5,
					],
					(object) [
						'string' => 'L Test String', 'integer' => 8,
					],
					(object) [
						'string' => 'Œ Test String', 'integer' => 6,
					],
					(object) [
						'string' => 'œ Test String', 'integer' => 7,
					],
					(object) [
						'string' => 'P Test String', 'integer' => 9,
					],
					(object) [
						'string' => 'p Test String', 'integer' => 10,
					],
				],
				'Should be sorted by the string,integer field in ascending order with caseinsensitive comparisons and fr_FR locale',
				false
			],
		];
	}

	/**
	 * Data provider for object inputs
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestToObject()
	{
		return [
			'single object' => [
				[
					'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
				],
				null,
				(object) [
					'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
				],
				'Should turn array into single object'
			],
			'multiple objects' => [
				[
					'first' => [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					'second' => [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					'third' => [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
				],
				null,
				(object) [
					'first' => (object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					'second' => (object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					'third' => (object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
				],
				'Should turn multiple dimension array into nested objects'
			],
			'single object with class' => [
				[
					'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
				],
				'stdClass',
				(object) [
					'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
				],
				'Should turn array into single object'
			],
			'multiple objects with class' => [
				[
					'first' => [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					'second' => [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					'third' => [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
				],
				'stdClass',
				(object) [
					'first' => (object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					'second' => (object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
					'third' => (object) [
						'integer' => 12, 'float' => 1.29999, 'string' => 'A Test String'
					],
				],
				'Should turn multiple dimension array into nested objects'
			],
		];
	}

	/**
	 * Data provider for object inputs
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestToArray()
	{
		return [
			'string' => [
				'foo',
				false,
				['foo']
			],
			'array' => [
				['foo'],
				false,
				['foo']
			],
			'array_recursive' => [
				['foo' => [
					(object) ['bar' => 'bar'],
					(object) ['baz' => 'baz']
				]],
				true,
				['foo' => [
					['bar' => 'bar'],
					['baz' => 'baz']
				]]
			],
			'iterator' => [
				['foo' => new \ArrayIterator(['bar' => 'baz'])],
				true,
				['foo' => ['bar' => 'baz']]
			]
		];
	}

	/**
	 * Tests the ArrayHelper::arrayUnique method.
	 *
	 * @param   array   $input     The array being input.
	 * @param   string  $expected  The expected return value.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestArrayUnique
	 * @covers        \Windwalker\Utilities\ArrayHelper::arrayUnique
	 * @since         2.0
	 */
	public function testArrayUnique($input, $expected)
	{
		$this->assertThat(
			ArrayHelper::arrayUnique($input),
			$this->equalTo($expected)
		);
	}

	/**
	 * Test pulling data from a single column (by index or association).
	 *
	 * @param   array   $input    Input array
	 * @param   mixed   $index    Column to pull, either by association or number
	 * @param   array   $expect   The expected results
	 * @param   string  $message  The failure message
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestGetColumn
	 * @covers        \Windwalker\Utilities\ArrayHelper::getColumn
	 * @since         2.0
	 */
	public function testGetColumn($input, $index, $expect, $message)
	{
		$this->assertEquals($expect, ArrayHelper::getColumn($input, $index), $message);
	}

	/**
	 * Test get value from an array.
	 *
	 * @param   array   $input     Input array
	 * @param   mixed   $index     Element to pull, either by association or number
	 * @param   mixed   $default   The defualt value, if element not present
	 * @param   string  $type      The type of value returned
	 * @param   array   $expect    The expected results
	 * @param   string  $message   The failure message
	 * @param   bool    $defaults  Use the defaults (true) or full argument list
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestGetValue
	 * @covers        \Windwalker\Utilities\ArrayHelper::getValue
	 * @since         2.0
	 */
	public function testGetValue($input, $index, $default, $type, $expect, $message, $defaults)
	{
		if ($defaults)
		{
			$output = ArrayHelper::getValue($input, $index);
		}
		else
		{
			$output = ArrayHelper::getValue($input, $index, $default, $type);
		}

		$this->assertEquals($expect, $output, $message);
	}

	/**
	 * Method to test setValue
	 *
	 * @covers \Windwalker\Utilities\ArrayHelper::setValue
	 *
	 * @return void
	 */
	public function testSetValue()
	{
		$data = [
			'Archer' => 'Unlimited Blade World',
			'Saber'  => 'Excalibur',
			'Lancer' => 'Gáe Bulg',
			'Rider'  => 'Breaker Gorgon',
		];
		$data2 = (object) $data;

		$newData = ArrayHelper::setValue($data, 'Saber', 'Avalon');

		$this->assertEquals('Avalon', $data['Saber']);
		$this->assertEquals('Avalon', $newData['Saber']);

		$newData = ArrayHelper::setValue($data, 'Archer', 'Unlimited Blade Works');

		$this->assertEquals('Unlimited Blade Works', $data['Archer']);
		$this->assertEquals('Unlimited Blade Works', $newData['Archer']);

		$newData = ArrayHelper::setValue($data, 'Berserker', 'Gold Hand');

		$this->assertEquals('Gold Hand', $data['Berserker']);
		$this->assertEquals('Gold Hand', $newData['Berserker']);

		$newData2 = ArrayHelper::setValue($data2, 'Saber', 'Avalon');

		$this->assertEquals('Avalon', $data2->Saber);
		$this->assertEquals('Avalon', $newData2->Saber);

		$newData2 = ArrayHelper::setValue($data2, 'Archer', 'Unlimited Blade Works');

		$this->assertEquals('Unlimited Blade Works', $data2->Archer);
		$this->assertEquals('Unlimited Blade Works', $newData2->Archer);

		$newData2 = ArrayHelper::setValue($data2, 'Berserker', 'Gold Hand');

		$this->assertEquals('Gold Hand', $data2->Berserker);
		$this->assertEquals('Gold Hand', $newData2->Berserker);
	}

	/**
	 * Tests the ArrayHelper::invert method.
	 *
	 * @param   array   $input     The array being input.
	 * @param   string  $expected  The expected return value.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestInvert
	 * @since         2.0
	 */
	public function testInvert($input, $expected)
	{
		$this->assertThat(
			ArrayHelper::invert($input),
			$this->equalTo($expected)
		);
	}

	/**
	 * Test the ArrayHelper::isAssociate method.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 * @sovers  ArrayHelper::isAssociative
	 */
	public function testIsAssociative()
	{
		$this->assertThat(
			ArrayHelper::isAssociative(
				[
					1, 2, 3
				]
			),
			$this->isFalse(),
			'Line: ' . __LINE__ . ' This array should not be associative.'
		);

		$this->assertThat(
			ArrayHelper::isAssociative(
				[
					'a' => 1, 'b' => 2, 'c' => 3
				]
			),
			$this->isTrue(),
			'Line: ' . __LINE__ . ' This array should be associative.'
		);

		$this->assertThat(
			ArrayHelper::isAssociative(
				[
					'a' => 1, 2, 'c' => 3
				]
			),
			$this->isTrue(),
			'Line: ' . __LINE__ . ' This array should be associative.'
		);
	}

	/**
	 * Tests the ArrayHelper::group method.
	 *
	 * @param   array   $source    The source array.
	 * @param   string  $key       Where the elements of the source array are objects or arrays, the key to pivot on.
	 * @param   array   $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestGroup
	 * @covers        \Windwalker\Utilities\ArrayHelper::group
	 * @since         2.0
	 */
	public function testGroup($source, $key, $expected)
	{
		$this->assertThat(
			ArrayHelper::group($source, $key),
			$this->equalTo($expected)
		);
	}

	/**
	 * Method to test pivot().
	 *
	 * @param array $data
	 * @param array $expected
	 *
	 * @return void
	 *
	 * @dataProvider seedTestPivot
	 * @covers       \Windwalker\Utilities\ArrayHelper::pivot
	 */
	public function testPivot($data, $expected)
	{
		$this->assertEquals($expected, ArrayHelper::pivot($data));
	}

	/**
	 * Test sorting an array of objects.
	 *
	 * @param   array    $input          Input array of objects
	 * @param   mixed    $key            Key to sort on
	 * @param   mixed    $direction      Ascending (1) or Descending(-1)
	 * @param   string   $casesensitive  @todo
	 * @param   string   $locale         @todo
	 * @param   array    $expect         The expected results
	 * @param   string   $message        The failure message
	 * @param   boolean  $defaults       Use the defaults (true) or full argument list
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestSortObject
	 * @covers        \Windwalker\Utilities\ArrayHelper::sortObjects
	 * @since         2.0
	 */
	public function testSortObjects($input, $key, $direction, $casesensitive, $locale, $expect, $message, $defaults, $swappable_keys = [])
	{
		// Convert the $locale param to a string if it is an array
		if (is_array($locale))
		{
			$locale = "'" . implode("', '", $locale) . "'";
		}

		if (empty($input))
		{
			$this->markTestSkipped('Skip for MAC until PHP sort bug is fixed');

			return;
		}
		elseif ($locale != false && !setlocale(LC_COLLATE, $locale))
		{
			// If the locale is not available, we can't have to transcode the string and can't reliably compare it.
			$this->markTestSkipped("Locale {$locale} is not available.");

			return;
		}

		if ($defaults)
		{
			$output = ArrayHelper::sortObjects($input, $key);
		}
		else
		{
			$output = ArrayHelper::sortObjects($input, $key, $direction, $casesensitive, $locale);
		}

		// The ordering of elements that compare equal according to
		// $key is undefined (implementation dependent).
		if ($expect != $output && $swappable_keys) {
			list($k1, $k2) = $swappable_keys;
			$e1 = $output[$k1];
			$e2 = $output[$k2];
			$output[$k1] = $e2;
			$output[$k2] = $e1;
		}

		$this->assertEquals($expect, $output, $message);
	}

	/**
	 * Test convert array to object.
	 *
	 * @param   string  $input      The array being input
	 * @param   string  $className  The class name to build
	 * @param   string  $expect     The expected return value
	 * @param   string  $message    The failure message
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestToObject
	 * @covers        \Windwalker\Utilities\ArrayHelper::toObject
	 * @since         2.0
	 */
	public function testToObject($input, $className, $expect, $message)
	{
		$this->assertEquals(
			$expect,
			ArrayHelper::toObject($input),
			$message
		);
	}

	/**
	 * testToArray
	 *
	 * @param $input
	 * @param $recursive
	 * @param $expect
	 *
	 * @return  void
	 *
	 * @dataProvider  seedTestToArray
	 * @covers        \Windwalker\Utilities\ArrayHelper::toArray
	 */
	public function testToArray($input, $recursive, $expect)
	{
		$this->assertEquals($expect, ArrayHelper::toArray($input, $recursive));
	}

	/**
	 * Tests the arraySearch method.
	 *
	 * @return  void
	 *
	 * @covers  \Windwalker\Utilities\ArrayHelper::arraySearch
	 * @since   2.0
	 */
	public function testArraySearch()
	{
		$array = [
			'name' => 'Foo',
			'email' => 'foobar@example.com'
		];

		// Search case sensitive.
		$this->assertEquals('name', ArrayHelper::arraySearch('Foo', $array));

		// Search case insenitive.
		$this->assertEquals('email', ArrayHelper::arraySearch('FOOBAR', $array, false));

		// Search non existent value.
		$this->assertEquals(false, ArrayHelper::arraySearch('barfoo', $array));
	}

	/**
	 * testFlatten
	 *
	 * @return  void
	 *
	 * @covers  \Windwalker\Utilities\ArrayHelper::flatten
	 * @since   2.0
	 */
	public function testFlatten()
	{
		$array = [
			'flower' => 'sakura',
			'olive' => 'peace',
			'pos1' => [
				'sunflower' => 'love'
			],
			'pos2' => [
				'cornflower' => 'elegant'
			]
		];

		$flatted = ArrayHelper::flatten($array);

		$this->assertEquals($flatted['pos1.sunflower'], 'love');

		$flatted = ArrayHelper::flatten($array, '/');

		$this->assertEquals($flatted['pos1/sunflower'], 'love');
	}

	/**
	 * Method to test query()
	 *
	 * @covers  \Windwalker\Utilities\ArrayHelper::query
	 *
	 * @return  void
	 */
	public function testQuery()
	{
		$data = [
			[
				'id' => 1,
				'title' => 'Julius Caesar',
				'data' => (object) ['foo' => 'bar'],
			],
			[
				'id' => 2,
				'title' => 'Macbeth',
				'data' => [],
			],
			[
				'id' => 3,
				'title' => 'Othello',
				'data' => 123,
			],
			[
				'id' => 4,
				'title' => 'Hamlet',
				'data' => true,
			],
		];

		// Test id equals
		$this->assertEquals([$data[1]], ArrayHelper::query($data, ['id' => 2]));

		// Test strict equals
		$this->assertEquals([$data[0], $data[2], $data[3]], ArrayHelper::query($data, ['data' => true], false));
		$this->assertEquals([$data[3]], ArrayHelper::query($data, ['data' => true], true));

		// Test id GT
		$this->assertEquals([$data[1], $data[2], $data[3]], ArrayHelper::query($data, ['id >' => 1]));

		// Test id GTE
		$this->assertEquals([$data[1], $data[2], $data[3]], ArrayHelper::query($data, ['id >=' => 2]));

		// Test id LT
		$this->assertEquals([$data[0], $data[1]], ArrayHelper::query($data, ['id <' => 3]));

		// Test id LTE
		$this->assertEquals([$data[0], $data[1]], ArrayHelper::query($data, ['id <=' => 2]));
		
		// Test in array
		$this->assertEquals([$data[0], $data[2]], ArrayHelper::query($data, ['id' => [1, 3]]));

		// Test array equals
		$this->assertEquals([$data[0]], ArrayHelper::query($data, ['id' => 1, 'title' => 'Julius Caesar']));

		// Test object equals
		$object = new \stdClass;
		$object->foo = 'bar';
		$this->assertEquals([$data[0], $data[3]], ArrayHelper::query($data, ['data' => $object]));

		// Test object strict equals
		$this->assertEquals([$data[0]], ArrayHelper::query($data, ['data' => $data[0]['data']], true));

		// Test Keep Key
		$this->assertEquals([1 => $data[1], 2 => $data[2], 3 => $data[3]], ArrayHelper::query($data, ['id >=' => 2], false, true));
	}

	/**
	 * Method to test query()
	 *
	 * @covers  \Windwalker\Utilities\ArrayHelper::query
	 *
	 * @return  void
	 */
	public function testQueryWithCallback()
	{
		$data = [
			[
				'id' => 1,
				'title' => 'Julius Caesar',
				'data' => (object) ['foo' => 'bar'],
			],
			[
				'id' => 2,
				'title' => 'Macbeth',
				'data' => [],
			],
			[
				'id' => 3,
				'title' => 'Othello',
				'data' => 123,
			],
			[
				'id' => 4,
				'title' => 'Hamlet',
				'data' => true,
			],
		];

		$results = ArrayHelper::query($data, function ($key, $value)
		{
		    return $value['title'] == 'Julius Caesar' || $value['id'] == 4;
		});

		$this->assertEquals([$data[0], $data[3]], $results);
	}

	/**
	 * testMatch
	 * 
	 * @covers \Windwalker\Utilities\ArrayHelper::match
	 *
	 * @return  void
	 */
	public function testMatch()
	{
		$data = [
			'id' => 1,
			'title' => 'Julius Caesar',
			'data' => (object) ['foo' => 'bar'],
		];

		$this->assertTrue(ArrayHelper::match($data, ['id' => 1]));
		$this->assertTrue(ArrayHelper::match($data, ['id' => [1, 2, 3]]));
		$this->assertTrue(ArrayHelper::match($data, ['id' => 1, 'title' => 'Julius Caesar']));
		$this->assertFalse(ArrayHelper::match($data, ['id' => 5]));
		$this->assertFalse(ArrayHelper::match($data, ['id' => 1, 'title' => 'Hamlet']));
	}

	/**
	 * Method to test mapKey
	 *
	 * @covers \Windwalker\Utilities\ArrayHelper::mapKey
	 *
	 * @return void
	 */
	public function testMapKey()
	{
		$data = [
			'top' => 'Captain America',
			'middle' => 'Iron Man',
			'bottom' => 'Thor',
		];
		$data2 = (object) $data;

		$map = [
			'middle' => 'bottom',
			'bottom' => 'middle',
		];

		$expected = [
			'top' => 'Captain America',
			'middle' => 'Thor',
			'bottom' => 'Iron Man',
		];
		$expected2 = (object) $expected;

		$result = ArrayHelper::mapKey($data, $map);
		$this->assertEquals($expected, $result);

		$result2 = ArrayHelper::mapKey($data2, $map);
		$this->assertEquals($expected2, $result2);
	}

	/**
	 * Method to test merge
	 *
	 * @covers \Windwalker\Utilities\ArrayHelper::merge
	 *
	 * @return void
	 */
	public function testMerge()
	{
		$data1 = [
			'green'     => 'Hulk',
			'red'       => 'empty',
			'human'     => [
				'dark'  => 'empty',
				'black' => [
					'male'      => 'empty',
					'female'    => 'empty',
					'no-gender' => 'empty',
				],
			]
		];
		$data2 = [
			'ai'        => 'Jarvis',
			'agent'     => 'Phil Coulson',
			'red'       => [
				'left'  => 'Pepper',
				'right' => 'Iron Man',
			],
			'human'     => [
				'dark'  => 'Nick Fury',
				'black' => [
					'female' => 'Black Widow',
					'male'   => 'Loki',
				],
			]
		];

		$expected = [
			'ai'        => 'Jarvis',
			'agent'     => 'Phil Coulson',
			'green' => 'Hulk',
			'red'       => [
				'left'  => 'Pepper',
				'right' => 'Iron Man',
			],
			'human'     => [
				'dark'  => 'Nick Fury',
				'black' => [
					'male'      => 'Loki',
					'female'    => 'Black Widow',
					'no-gender' => 'empty',
				],
			],
		];

		$expected2 = [
			'ai'        => 'Jarvis',
			'agent'     => 'Phil Coulson',
			'green' => 'Hulk',
			'red'       => [
				'left'  => 'Pepper',
				'right' => 'Iron Man',
			],
			'human'     => [
				'dark'  => 'Nick Fury',
				'black' => [
					'male'   => 'Loki',
					'female' => 'Black Widow',
				],
			],
		];

		$this->assertEquals($expected, ArrayHelper::merge($data1, $data2));
		$this->assertEquals($expected2, ArrayHelper::merge($data1, $data2, false));
	}

	/**
	 * testGetByPath
	 *
	 * @return  void
	 *
	 * @covers \Windwalker\Utilities\ArrayHelper::getByPath
	 */
	public function testGetByPath()
	{
		$data = [
			'flower' => 'sakura',
			'olive' => 'peace',
			'pos1' => [
				'sunflower' => 'love'
			],
			'pos2' => [
				'cornflower' => 'elegant'
			],
			'array' => [
				'A',
				'B',
				'C'
			]
		];

		$this->assertEquals('sakura', ArrayHelper::getByPath($data, 'flower'));
		$this->assertEquals('love', ArrayHelper::getByPath($data, 'pos1.sunflower'));
		$this->assertEquals('love', ArrayHelper::getByPath($data, 'pos1/sunflower', '/'));
		$this->assertEquals($data['array'], ArrayHelper::getByPath($data, 'array'));
		$this->assertNull(ArrayHelper::getByPath($data, 'not.exists'));
	}

	/**
	 * testSetByPath
	 *
	 * @return  void
	 *
	 * @covers \Windwalker\Utilities\ArrayHelper::setByPath
	 */
	public function testSetByPath()
	{
		$data = [];

		// One level
		$return = ArrayHelper::setByPath($data, 'flower', 'sakura');

		$this->assertEquals('sakura', $data['flower']);
		$this->assertTrue($return);

		// Multi-level
		ArrayHelper::setByPath($data, 'foo.bar', 'test');

		$this->assertEquals('test', $data['foo']['bar']);

		// Separator
		ArrayHelper::setByPath($data, 'foo/bar', 'play', '/');

		$this->assertEquals('play', $data['foo']['bar']);

		// Type
		ArrayHelper::setByPath($data, 'cloud/fly', 'bird', '/', 'stdClass');

		$this->assertEquals('bird', $data['cloud']->fly);

		// False
		$return = ArrayHelper::setByPath($data, '', 'goo');

		$this->assertFalse($return);

		// Fix path
		ArrayHelper::setByPath($data, 'double..separators', 'value');

		$this->assertEquals('value', $data['double']['separators']);

		$this->assertExpectedException(function()
		{
			ArrayHelper::setByPath($data, 'a.b', 'c', '.', 'Non\Exists\Class');
		}, new \InvalidArgumentException, 'Type or class: Non\Exists\Class not exists');
	}

	/**
	 * testRemoveByPath
	 *
	 * @return  void
	 */
	public function testRemoveByPath()
	{
		$data = [
			'foo' => [
				'bar' => '123'
			]
		];

		ArrayHelper::removeByPath($data, 'foo.bar');

		$this->assertFalse(array_key_exists('bar', $data['foo']));

		$data = [
			'foo' => [
				'bar' => '123'
			]
		];

		ArrayHelper::removeByPath($data, 'foo');

		$this->assertFalse(array_key_exists('foo', $data));

		$data = [
			'foo' => [
				'bar' => '123'
			]
		];

		ArrayHelper::removeByPath($data, 'foo.yoo');

		$this->assertEquals('123', $data['foo']['bar']);

		$data = (object) [
			'foo' => (object) [
				'bar' => '123'
			]
		];

		ArrayHelper::removeByPath($data, 'foo.bar');

		$this->assertFalse(property_exists($data->foo, 'bar'));
	}
}
