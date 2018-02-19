<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Crypt\Test;

use Windwalker\Crypt\Password;

/**
 * Test class of Password
 *
 * @since 2.0
 */
class PasswordTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instance.
     *
     * @var Password
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->instance = new Password(Password::BLOWFISH, 10, 'sakura');
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
     * Method to test create().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Password::create
     * @covers \Windwalker\Crypt\Password::verify
     */
    public function testCreateMd5()
    {
        $this->instance->setType(Password::MD5);

        $pass = $this->instance->create('windwalker');

        $this->assertEquals(crypt('windwalker', '$1$sakura$'), $pass);

        $this->assertTrue($this->instance->verify('windwalker', $pass));

        // Use default
        $password = new Password;

        $this->assertTrue($password->verify('windwalker', $password->create('windwalker')));
    }

    /**
     * Method to test create().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Password::create
     * @covers \Windwalker\Crypt\Password::verify
     */
    public function testCreateSha256()
    {
        $this->instance->setType(Password::SHA256);

        $this->instance->setCost(5000);

        $pass = $this->instance->create('windwalker');

        $this->assertEquals(crypt('windwalker', '$5$rounds=5000$sakura$'), $pass);

        $this->assertTrue($this->instance->verify('windwalker', $pass));

        // Cost less than 1000 will be 1000
        $this->instance->setCost(125);

        $pass = $this->instance->create('windwalker', Password::SHA256);

        $this->assertEquals(crypt('windwalker', '$5$rounds=1000$sakura$'), $pass);

        $this->assertTrue($this->instance->verify('windwalker', $pass));

        // Use default
        $password = new Password;

        $this->assertTrue($password->verify('windwalker', $password->create('windwalker')));
    }

    /**
     * Method to test create().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Password::create
     * @covers \Windwalker\Crypt\Password::verify
     */
    public function testCreateSha512()
    {
        $this->instance->setType(Password::SHA512);

        $this->instance->setCost(5000);

        $pass = $this->instance->create('windwalker');

        $this->assertEquals(crypt('windwalker', '$6$rounds=5000$sakura$'), $pass);

        $this->assertTrue($this->instance->verify('windwalker', $pass));

        // Cost less than 1000 will be 1000
        $this->instance->setCost(125);

        $pass = $this->instance->create('windwalker');

        $this->assertEquals(crypt('windwalker', '$6$rounds=1000$sakura$'), $pass);

        $this->assertTrue($this->instance->verify('windwalker', $pass));

        // Use default
        $password = new Password;

        $this->assertTrue($password->verify('windwalker', $password->create('windwalker')));
    }

    /**
     * Method to test create().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Password::create
     * @covers \Windwalker\Crypt\Password::verify
     */
    public function testCreateBlowfish()
    {
        $this->instance->setType(Password::BLOWFISH);

        $pass = $this->instance->create('windwalker');

        $prefix = (version_compare(PHP_VERSION, '5.3.7') >= 0) ? '$2y$' : '$2a$';

        // PHP7 will auto generate salt
        if (version_compare(PHP_VERSION, 7, '<')) {
            $this->assertEquals(crypt('windwalker', $prefix . '10$sakurasakurasakurasaku$'), $pass);
        }

        $this->assertTrue($this->instance->verify('windwalker', $pass));

        // Use default
        $password = new Password;

        $this->assertTrue($password->verify('windwalker', $password->create('windwalker')));
    }

    /**
     * testCreateArgon2
     *
     * @return  void
     */
    public function testCreateArgon2()
    {
        if (!extension_loaded('libsodium')) {
            self::markTestSkipped('Libsodium-php not installed.');
        }

        $this->instance->setType(Password::SODIUM_ARGON2)->setSalt(null);

        $pass = $this->instance->create('windwalker');

        $this->assertTrue($this->instance->verify('windwalker', $pass));

        // Use default
        $password = new Password;

        $this->assertTrue($password->verify('windwalker', $password->create('windwalker')));
    }

    /**
     * testCreateArgon2
     *
     * @return  void
     */
    public function testCreateScrypt()
    {
        if (!extension_loaded('libsodium')) {
            self::markTestSkipped('Libsodium-php not installed.');
        }

        $this->instance->setType(Password::SODIUM_SCRYPT)->setSalt(null);

        $pass = $this->instance->create('windwalker');

        $this->assertTrue($this->instance->verify('windwalker', $pass));

        // Use default
        $password = new Password;

        $this->assertTrue($password->verify('windwalker', $password->create('windwalker')));
    }

    /**
     * Method to test getSalt().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Password::getSalt
     * @TODO   Implement testGetSalt().
     */
    public function testGetSalt()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setSalt().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Password::setSalt
     * @TODO   Implement testSetSalt().
     */
    public function testSetSalt()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test getCost().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Password::getCost
     * @TODO   Implement testGetCost().
     */
    public function testGetCost()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Method to test setCost().
     *
     * @return void
     *
     * @covers \Windwalker\Crypt\Password::setCost
     * @TODO   Implement testSetCost().
     */
    public function testSetCost()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
