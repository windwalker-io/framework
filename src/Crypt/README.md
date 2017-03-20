# Windwalker Crypt

Windwalker Crypt package is a wrap of PHP openssl to hash and verify password,
and provides an easy interface to do Symmetric-Key Algorithm encryption.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/crypt": "~3.0"
    }
}
```

## Password Hashing

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
$password = new Password(Password::SHA512, 5000, md5('to be or not to be.'));

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

The `Crypt` object provides different ciphers to encrypt/decrypt your data. Most of these ciphers must use
PHP openssl functions to work. If your PHP are not available for openssl extension, you can use `PhpAesCipher`
as default cipher, it is a native PHP implementation of AES by [aes.class.php](https://gist.github.com/chrisns/3992815).

### Use Cipher

``` php
use Windwalker\Crypt\Cipher\BlowfishCipher;
use Windwalker\Crypt\Crypt;

$crypt = new Crypt(new BlowfishCipher, 'My private key');

$encrypted = $crypt->encrypt('My Text');

$bool = $crypt->verify('My Text', $encrypted, 'My private key'); // True
```

Get the plain text back:

``` php
$crypt = new Crypt(new BlowfishCipher, 'My private key');

$encrypted = $crypt->encrypt('My Text');

$text = $crypt->decrypt($encrypted);
```

### Custom Cipher

You can set mode to cipher.

``` php
$cipher = new BlowfishCipher($key);

$cipher->setMode('ecb');

$cipher->encrypt(...);
```

Or set the PBKDF2 iteration count.

``` php
$cipher = new BlowfishCipher($key, array('pbkdf2_iteration' => 64000)); // Default is 12000
```

### Available Ciphers

- [BlowfishCipher](http://en.wikipedia.org/wiki/Blowfish_(cipher))
- [Aes56Cipher](http://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
- [Des3Cipher](http://en.wikipedia.org/wiki/Triple_DES)
- PhpAesCipher - Only use this when system not support openssl extension.
