# Windwalker Crypt

Windwalker Crypt package is use to encrypt & verify password, and provides an easy interface to do Symmetric-Key Algorithm encryption.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/crypt": "~2.0"
    }
}
```

## Password Encrypting

`Password` object is a simple object to encrypt user's password, it is impossible to decrypt password hash, `Password` object
uses a one-way algorithm.

### Create Password

``` php
use Windwalker\Crypt\Password;

$password = new Password;

$pass = $password->create('pass1234');

// $2y$10$csNfML/FJlKwaHR8xREgZuhp0pqSqeg.jdACqDsKO/MCHDkTuIZEa
```

Using other hash algorithm

``` php
use Windwalker\Crypt\Password;

$password = new Password(Password::SHA256);

$pass = $password->create('pass1234');
```

Set cost and salt:

``` php
use Windwalker\Crypt\Password;

// The Blowfish algorithm should set cost number between 4 to 31.
// We are suggest not higher than 15, else it will be too slow.
$password = new Password(Password::BLOWFISH, 15, md5('to be or not to be.'));

$pass = $password->create('pass1234');

// Note the Sha256 and Sha512 should set cost number higher than 1000
$password = new Password(Password::BLOWFISH, 5000, md5('to be or not to be.'));

$pass = $password->create('pass1234');
```

### Available algorithms
 
- Password::MD5
- Password::BLOWFISH
- Password::SHA256
- Password::SHA512

### Verify Password

We don't need to care the hash algorithm, Password object will auto detect the algorithm type:

``` php
$bool = $password->verify('pass1234', $pass);
```

## Symmetric-Key Algorithm Encryption

The `Crypt` object provides some Ciphers to encrypt our text. You must install PHP Mcrypt extension.

But there has a `CipherSimple` are can use if your server is not able to install Mcrypt.

### Mcrypt Cipher

``` php
use Windwalker\Crypt\Cipher\CipherBlowfish;
use Windwalker\Crypt\Crypt;

$crypt = new Crypt(new CipherBlowfish, 'My private key');

$encrypted = $crypt->encrypt('My Text');

$bool = $crypt->verify('My Text', $encrypted, 'My private key'); // True
```

Get the plain text back:

``` php
$crypt = new Crypt(new CipherBlowfish, 'My private key');

$encrypted = $crypt->encrypt('My Text');

$text = $crypt->decrypt($encrypted);
```

### Available Ciphers

- [CipherBlowfish](http://en.wikipedia.org/wiki/Blowfish_(cipher))
- [CipherRijndael256](http://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
- [Cipher3DES](http://en.wikipedia.org/wiki/Triple_DES)
- CipherSimple - Only use this when system not support mcrypt. 

### Installing Mcrypt

Install Mcrypt on OSX: http://goo.gl/s8O1SH
