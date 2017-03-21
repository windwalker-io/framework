<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Utilities\Test;

use \Windwalker\String\Utf8String;

/**
 * Test class of String
 *
 * @since 2.0
 */
class Utf8StringTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @var    String
	 * @since  2.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestIs_ascii()
	{
		return [
			['ascii', true],
			['1024', true],
			['#$#@$%', true],
			['áÑ', false],
			['ÿ©', false],
			['¡¾', false],
			['÷™', false],
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestStrpos()
	{
		return [
			[3, 'missing', 'sing', 0],
			[false, 'missing', 'sting', 0],
			[4, 'missing', 'ing', 0],
			[10, ' объектов на карте с', 'на карте', 0],
			[0, 'на карте с', 'на карте', 0, 0],
			[false, 'на карте с', 'на каррте', 0],
			[false, 'на карте с', 'на карте', 2],
			[3, 'missing', 'sing', false]
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestGetStrrpos()
	{
		return [
			[3, 'missing', 'sing', 0],
			[false, 'missing', 'sting', 0],
			[4, 'missing', 'ing', 0],
			[10, ' объектов на карте с', 'на карте', 0],
			[0, 'на карте с', 'на карте', 0],
			[false, 'на карте с', 'на каррте', 0],
			[3, 'на карте с', 'карт', 2]
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestSubstr()
	{
		return [
			['issauga', 'Mississauga', 4, false],
			['на карте с', ' объектов на карте с', 10, false],
			['на ка', ' объектов на карте с', 10, 5],
			['те с', ' объектов на карте с', -4, false],
			[false, ' объектов на карте с', 99, false]
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestStrtolower()
	{
		return [
			['Windwalker! Rocks', 'windwalker! rocks']
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestStrtoupper()
	{
		return [
			['Windwalker! Rocks', 'WINDWALKER! ROCKS']
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestStrlen()
	{
		return [
			['Windwalker! Rocks', 17]
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestStr_ireplace()
	{
		return [
			['Pig', 'cow', 'the pig jumped', false, 'the cow jumped'],
			['Pig', 'cow', 'the pig jumped', true, 'the cow jumped'],
			['Pig', 'cow', 'the pig jumped over the cow', true, 'the cow jumped over the cow'],
			[['PIG', 'JUMPED'], ['cow', 'hopped'], 'the pig jumped over the pig', true, 'the cow hopped over the cow'],
			['шил', 'биш', 'Би шил идэй чадна', true, 'Би биш идэй чадна'],
			['/', ':', '/test/slashes/', true, ':test:slashes:'],
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestStr_split()
	{
		return [
			['string', 1, ['s', 't', 'r', 'i', 'n', 'g']],
			['string', 2, ['st', 'ri', 'ng']],
			['волн', 3, ['вол', 'н']],
			['волн', 1, ['в', 'о', 'л', 'н']]
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestStrcasecmp()
	{
		return [
			['THIS IS STRING1', 'this is string1', false, 0],
			['this is string1', 'this is string2', false, -1],
			['this is string2', 'this is string1', false, 1],
			['бгдпт', 'бгдпт', false, 0],
			['àbc', 'abc', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], 1],
			['àbc', 'bcd', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], -1],
			['é', 'è', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], -1],
			['É', 'é', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], 0],
			['œ', 'p', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], -1],
			['œ', 'n', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], 1],
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestStrcmp()
	{
		return [
			['THIS IS STRING1', 'this is string1', false, -1],
			['this is string1', 'this is string2', false, -1],
			['this is string2', 'this is string1', false, 1],
			['a', 'B', false, 1],
			['A', 'b', false, -1],
			['Àbc', 'abc', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], 1],
			['Àbc', 'bcd', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], -1],
			['É', 'è', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], -1],
			['é', 'È', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], -1],
			['Œ', 'p', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], -1],
			['Œ', 'n', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], 1],
			['œ', 'N', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], 1],
			['œ', 'P', ['fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR.UTF-8@euro', 'French_Standard', 'french', 'fr_FR', 'fre_FR'], -1],
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestStrcspn()
	{
		return [
			['subject <a> string <a>', '<>', false, false, 8],
			['Би шил {123} идэй {456} чадна', '}{', null, false, 7],
			['Би шил {123} идэй {456} чадна', '}{', 13, 10, 5]
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestStristr()
	{
		return [
			['haystack', 'needle', false],
			['before match, after match', 'match', 'match, after match'],
			['Би шил идэй чадна', 'шил', 'шил идэй чадна']
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestStrrev()
	{
		return [
			['abc def', 'fed cba'],
			['Би шил', 'лиш иБ']
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestStrspn()
	{
		return [
			['A321 Main Street', '0123456789', 1, 2, 2],
			['321 Main Street', '0123456789', null, 2, 2],
			['A321 Main Street', '0123456789', null, 10, 0],
			['321 Main Street', '0123456789', null, null, 3],
			['Main Street 321', '0123456789', null, -3, 0],
			['321 Main Street', '0123456789', null, -13, 2],
			['321 Main Street', '0123456789', null, -12, 3],
			['A321 Main Street', '0123456789', 0, null, 0],
			['A321 Main Street', '0123456789', 1, 10, 3],
			['A321 Main Street', '0123456789', 1, null, 3],
			['Би шил идэй чадна', 'Би', null, null, 2],
			['чадна Би шил идэй чадна', 'Би', null, null, 0]
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestSubstr_replace()
	{
		return [
			['321 Broadway Avenue', '321 Main Street', 'Broadway Avenue', 4, null],
			['321 Broadway Street', '321 Main Street', 'Broadway', 4, 4],
			['чадна 我能吞', 'чадна Би шил идэй чадна', '我能吞', 6, null],
			['чадна 我能吞 шил идэй чадна', 'чадна Би шил идэй чадна', '我能吞', 6, 2]
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestLtrim()
	{
		return [
			['   abc def', null, 'abc def'],
			['   abc def', '', '   abc def'],
			[' Би шил', null, 'Би шил'],
			["\t\n\r\x0BБи шил", null, 'Би шил'],
			["\x0B\t\n\rБи шил", "\t\n\x0B", "\rБи шил"],
			["\x09Би шил\x0A", "\x09\x0A", "Би шил\x0A"],
			['1234abc', '0123456789', 'abc']
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestRtrim()
	{
		return [
			['abc def   ', null, 'abc def'],
			['abc def   ', '', 'abc def   '],
			['Би шил ', null, 'Би шил'],
			["Би шил\t\n\r\x0B", null, 'Би шил'],
			["Би шил\r\x0B\t\n", "\t\n\x0B", "Би шил\r"],
			["\x09Би шил\x0A", "\x09\x0A", "\x09Би шил"],
			['1234abc', 'abc', '01234']
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestTrim()
	{
		return [
			['  abc def   ', null, 'abc def'],
			['  abc def   ', '', '  abc def   '],
			['   Би шил ', null, 'Би шил'],
			["\t\n\r\x0BБи шил\t\n\r\x0B", null, 'Би шил'],
			["\x0B\t\n\rБи шил\r\x0B\t\n", "\t\n\x0B", "\rБи шил\r"],
			["\x09Би шил\x0A", "\x09\x0A", "Би шил"],
			['1234abc56789', '0123456789', 'abc']
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestUcfirst()
	{
		return [
			['george', null, null, 'George'],
			['мога', null, null, 'Мога'],
			['ψυχοφθόρα', null, null, 'Ψυχοφθόρα'],
			['dr jekill and mister hyde', ' ', null, 'Dr Jekill And Mister Hyde'],
			['dr jekill and mister hyde', ' ', '_', 'Dr_Jekill_And_Mister_Hyde'],
			['dr jekill and mister hyde', ' ', '', 'DrJekillAndMisterHyde'],
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestUcwords()
	{
		return [
			['george washington', 'George Washington'],
			["george\r\nwashington", "George\r\nWashington"],
			['мога', 'Мога'],
			['αβγ δεζ', 'Αβγ Δεζ'],
			['åbc öde', 'Åbc Öde']
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestTranscode()
	{
		return [
			['Åbc Öde €2.0', 'UTF-8', 'ISO-8859-1', "\xc5bc \xd6de EUR2.0"],
			[['Åbc Öde €2.0'], 'UTF-8', 'ISO-8859-1', null],
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestValid()
	{
		return [
			["\xCF\xB0", true],
			["\xFBa", false],
			["\xFDa", false],
			["foo\xF7bar", false],
			['george Мога Ž Ψυχοφθόρα ฉันกินกระจกได้ 我能吞下玻璃而不伤身体 ', true],
			["\xFF ABC", false],
			["0xfffd ABC", true],
			['', true]
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestUnicodeToUtf8()
	{
		return [
			["\u0422\u0435\u0441\u0442 \u0441\u0438\u0441\u0442\u0435\u043c\u044b", "Тест системы"],
			["\u00dcberpr\u00fcfung der Systemumstellung", "Überprüfung der Systemumstellung"]
		];
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   2.0
	 */
	public function seedTestUnicodeToUtf16()
	{
		return [
			["\u0422\u0435\u0441\u0442 \u0441\u0438\u0441\u0442\u0435\u043c\u044b", "Тест системы"],
			["\u00dcberpr\u00fcfung der Systemumstellung", "Überprüfung der Systemumstellung"]
		];
	}

	/**
	 * Test...
	 *
	 * @param   string   $string    @todo
	 * @param   boolean  $expected  @todo
	 *
	 * @return  void
	 *
	 * @covers        \Windwalker\String\Utf8String::is_ascii
	 * @dataProvider  seedTestIs_ascii
	 * @since         2.0
	 */
	public function testIs_ascii($string, $expected)
	{
		$this->assertEquals(
			$expected,
			Utf8String::is_ascii($string)
		);
	}

	/**
	 * Test...
	 *
	 * @param   string   $expect    @todo
	 * @param   string   $haystack  @todo
	 * @param   string   $needle    @todo
	 * @param   integer  $offset    @todo
	 *
	 * @return  void
	 *
	 * @covers        \Windwalker\String\Utf8String::strpos
	 * @dataProvider  seedTestStrpos
	 * @since         2.0
	 */
	public function testStrpos($expect, $haystack, $needle, $offset = 0)
	{
		$actual = Utf8String::strpos($haystack, $needle, $offset);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string   $expect    @todo
	 * @param   string   $haystack  @todo
	 * @param   string   $needle    @todo
	 * @param   integer  $offset    @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::strrpos
	 * @dataProvider  seedTestGetStrrpos
	 * @since         2.0
	 */
	public function testStrrpos($expect, $haystack, $needle, $offset = 0)
	{
		$actual = Utf8String::strrpos($haystack, $needle, $offset);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string    $expect  @todo
	 * @param   string    $string  @todo
	 * @param   string    $start   @todo
	 * @param   bool|int  $length  @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::substr
	 * @dataProvider  seedTestSubstr
	 * @since         2.0
	 */
	public function testSubstr($expect, $string, $start, $length = false)
	{
		$actual = Utf8String::substr($string, $start, $length);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::strtolower
	 * @dataProvider  seedTestStrtolower
	 * @since         2.0
	 */
	public function testStrtolower($string, $expect)
	{
		$actual = Utf8String::strtolower($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::strtoupper
	 * @dataProvider  seedTestStrtoupper
	 * @since         2.0
	 */
	public function testStrtoupper($string, $expect)
	{
		$actual = Utf8String::strtoupper($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::strlen
	 * @dataProvider  seedTestStrlen
	 * @since         2.0
	 */
	public function testStrlen($string, $expect)
	{
		$actual = Utf8String::strlen($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string   $search   @todo
	 * @param   string   $replace  @todo
	 * @param   string   $subject  @todo
	 * @param   integer  $count    @todo
	 * @param   string   $expect   @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::str_ireplace
	 * @dataProvider  seedTestStr_ireplace
	 * @since         2.0
	 */
	public function testStr_ireplace($search, $replace, $subject, $count, $expect)
	{
		$actual = Utf8String::str_ireplace($search, $replace, $subject, $count);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string        @todo
	 * @param   string  $split_length  @todo
	 * @param   string  $expect        @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::str_split
	 * @dataProvider  seedTestStr_split
	 * @since         2.0
	 */
	public function testStr_split($string, $split_length, $expect)
	{
		$actual = Utf8String::str_split($string, $split_length);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string1  @todo
	 * @param   string  $string2  @todo
	 * @param   string  $locale   @todo
	 * @param   string  $expect   @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::strcasecmp
	 * @dataProvider  seedTestStrcasecmp
	 * @since         2.0
	 */
	public function testStrcasecmp($string1, $string2, $locale, $expect)
	{
		// Convert the $locale param to a string if it is an array
		if (is_array($locale))
		{
			$locale = "'" . implode("', '", $locale) . "'";
		}

		if (substr(php_uname(), 0, 6) == 'Darwin' && $locale != false)
		{
			$this->markTestSkipped('Darwin bug prevents foreign conversion from working properly');
		}
		elseif ($locale != false && !setlocale(LC_COLLATE, $locale))
		{
			$this->markTestSkipped("Locale {$locale} is not available.");
		}
		else
		{
			$actual = Utf8String::strcasecmp($string1, $string2, $locale);

			if ($actual != 0)
			{
				$actual = $actual / abs($actual);
			}

			$this->assertEquals($expect, $actual);
		}
	}

	/**
	 * Test...
	 *
	 * @param   string  $string1  @todo
	 * @param   string  $string2  @todo
	 * @param   string  $locale   @todo
	 * @param   string  $expect   @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::strcmp
	 * @dataProvider  seedTestStrcmp
	 * @since         2.0
	 */
	public function testStrcmp($string1, $string2, $locale, $expect)
	{
		// Convert the $locale param to a string if it is an array
		if (is_array($locale))
		{
			$locale = "'" . implode("', '", $locale) . "'";
		}

		if (substr(php_uname(), 0, 6) == 'Darwin' && $locale != false)
		{
			$this->markTestSkipped('Darwin bug prevents foreign conversion from working properly');
		}
		elseif ($locale != false && !setlocale(LC_COLLATE, $locale))
		{
			// If the locale is not available, we can't have to transcode the string and can't reliably compare it.
			$this->markTestSkipped("Locale {$locale} is not available.");
		}
		else
		{
			$actual = Utf8String::strcmp($string1, $string2, $locale);

			if ($actual != 0)
			{
				$actual = $actual / abs($actual);
			}

			$this->assertEquals($expect, $actual);
		}
	}

	/**
	 * Test...
	 *
	 * @param   string   $haystack  @todo
	 * @param   string   $needles   @todo
	 * @param   integer  $start     @todo
	 * @param   integer  $len       @todo
	 * @param   string   $expect    @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::strcspn
	 * @dataProvider  seedTestStrcspn
	 * @since         2.0
	 */
	public function testStrcspn($haystack, $needles, $start, $len, $expect)
	{
		$actual = Utf8String::strcspn($haystack, $needles, $start, $len);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $haystack  @todo
	 * @param   string  $needle    @todo
	 * @param   string  $expect    @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::stristr
	 * @dataProvider  seedTestStristr
	 * @since         2.0
	 */
	public function testStristr($haystack, $needle, $expect)
	{
		$actual = Utf8String::stristr($haystack, $needle);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::strrev
	 * @dataProvider  seedTestStrrev
	 * @since         2.0
	 */
	public function testStrrev($string, $expect)
	{
		$actual = Utf8String::strrev($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string   $subject  @todo
	 * @param   string   $mask     @todo
	 * @param   integer  $start    @todo
	 * @param   integer  $length   @todo
	 * @param   string   $expect   @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::strspn
	 * @dataProvider  seedTestStrspn
	 * @since         2.0
	 */
	public function testStrspn($subject, $mask, $start, $length, $expect)
	{
		$actual = Utf8String::strspn($subject, $mask, $start, $length);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string   $expect       @todo
	 * @param   string   $string       @todo
	 * @param   string   $replacement  @todo
	 * @param   integer  $start        @todo
	 * @param   integer  $length       @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::substr_replace
	 * @dataProvider  seedTestSubstr_replace
	 * @since         2.0
	 */
	public function testSubstr_replace($expect, $string, $replacement, $start, $length)
	{
		$actual = Utf8String::substr_replace($string, $replacement, $start, $length);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string    @todo
	 * @param   string  $charlist  @todo
	 * @param   string  $expect    @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::ltrim
	 * @dataProvider  seedTestLtrim
	 * @since         2.0
	 */
	public function testLtrim($string, $charlist, $expect)
	{
		if ($charlist === null)
		{
			$actual = Utf8String::ltrim($string);
		}
		else
		{
			$actual = Utf8String::ltrim($string, $charlist);
		}

		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string    @todo
	 * @param   string  $charlist  @todo
	 * @param   string  $expect    @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::rtrim
	 * @dataProvider  seedTestRtrim
	 * @since         2.0
	 */
	public function testRtrim($string, $charlist, $expect)
	{
		if ($charlist === null)
		{
			$actual = Utf8String::rtrim($string);
		}
		else
		{
			$actual = Utf8String::rtrim($string, $charlist);
		}

		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string    @todo
	 * @param   string  $charlist  @todo
	 * @param   string  $expect    @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::trim
	 * @dataProvider  seedTestTrim
	 * @since         2.0
	 */
	public function testTrim($string, $charlist, $expect)
	{
		if ($charlist === null)
		{
			$actual = Utf8String::trim($string);
		}
		else
		{
			$actual = Utf8String::trim($string, $charlist);
		}

		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string        @todo
	 * @param   string  $delimiter     @todo
	 * @param   string  $newDelimiter  @todo
	 * @param   string  $expect        @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::ucfirst
	 * @dataProvider  seedTestUcfirst
	 * @since         2.0
	 */
	public function testUcfirst($string, $delimiter, $newDelimiter, $expect)
	{
		$actual = Utf8String::ucfirst($string, $delimiter, $newDelimiter);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::ucwords
	 * @dataProvider  seedTestUcwords
	 * @since         2.0
	 */
	public function testUcwords($string, $expect)
	{
		$actual = Utf8String::ucwords($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $source         @todo
	 * @param   string  $from_encoding  @todo
	 * @param   string  $to_encoding    @todo
	 * @param   string  $expect         @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::transcode
	 * @dataProvider  seedTestTranscode
	 * @since         2.0
	 */
	public function testTranscode($source, $from_encoding, $to_encoding, $expect)
	{
		$actual = Utf8String::transcode($source, $from_encoding, $to_encoding);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::valid
	 * @dataProvider  seedTestValid
	 * @since         2.0
	 */
	public function testValid($string, $expect)
	{
		$actual = Utf8String::valid($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::unicode_to_utf8
	 * @dataProvider  seedTestUnicodeToUtf8
	 * @since         2.0
	 */
	public function testUnicodeToUtf8($string, $expect)
	{
		$actual = Utf8String::unicode_to_utf8($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::unicode_to_utf16
	 * @dataProvider  seedTestUnicodeToUtf16
	 * @since         2.0
	 */
	public function testUnicodeToUtf16($string, $expect)
	{
		$actual = Utf8String::unicode_to_utf16($string);
		$this->assertEquals($expect, $actual);
	}

	/**
	 * Test...
	 *
	 * @param   string  $string  @todo
	 * @param   string  $expect  @todo
	 *
	 * @return  array
	 *
	 * @covers        \Windwalker\String\Utf8String::compliant
	 * @dataProvider  seedTestValid
	 * @since         2.0
	 */
	public function testCompliant($string, $expect)
	{
		$actual = Utf8String::compliant($string);
		$this->assertEquals($expect, $actual);
	}
}
