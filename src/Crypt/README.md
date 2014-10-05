# Windwalker Crypt Package

Windwalker Crypt package is using to hash/verify password, and provides an easy interface to make Symmetric-Key Algorithm.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/crypt": "~2.0"
    }
}
```

## Password Hashing

### Create Password

``` php
use Windwalker\Crypt\Password;

$password = new Password;

$pass = $password->create('pass1234');

// $2y$10$csNfML/FJlKwaHR8xREgZuhp0pqSqeg.jdACqDsKO/MCHDkTuIZEa
```

Using other hash type

``` php
use Windwalker\Crypt\Password;

$password = new Password(Password::SHA256);

$pass = $password->create('pass1234');
```

Set cost and salt:

``` php
use Windwalker\Crypt\Password;

// The Blowfish should set cost between 4 to 31.
// We are suggest not higher than 15, or it will be too slow.
$password = new Password(Password::BLOWFISH, 15, md5('to be or not to be.'));

$pass = $password->create('pass1234');

// Note the Sha256 and Sha512 should set cost higher than 1000
$password = new Password(Password::BLOWFISH, 5000, md5('to be or not to be.'));

$pass = $password->create('pass1234');
```

### Verify

We don't need to care the hash type, Password object will auto detect the type:

``` php
$bool = $password->verify('pass1234', $pass);
```

## Symmetric-Key Algorithm

The `Crypt` object provides some Ciphers to algorithm our text. You must install PHP Mcrypt extension.

But there has a `CipherSimple` can use if your server is not able to install Mcrypt.

### Mcrypt Cipher

``` php
use Windwalker\Crypt\Cipher\CipherBlowfish;
use Windwalker\Crypt\Crypt;

$crypt = new Crypt(new BlowfishCipher, 'My private key');

$encrypted = $crypt->encrypt('My Text');

$bool = $crypt->verify('My Text', $hash, 'My private key'); // True
```

Get the plain text back:

``` php
$crypt = new Crypt(new BlowfishCipher, 'My private key');

$encrypted = $crypt->encrypt('My Text');

$text = $crypt->decrypt($encrypted);
```

### Supported Cipher

- [Blowfish](http://en.wikipedia.org/wiki/Blowfish_(cipher))
- [Rijndael256](http://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
- [3DES](http://en.wikipedia.org/wiki/Triple_DES)
- Simple - Only use this when system not support mcrypt. 

### Installing Mcrypt

Install Mcrypt on OSX: http://topicdesk.com/downloads/mcrypt/mcrypt-download
