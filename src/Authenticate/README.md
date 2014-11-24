# Windwalker Authenticate

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/authenticate": "~2.0"
    }
}
```

## Getting Started

This is a simple login auth process.

``` php
public function login($username, $password)
{
    $auth = new Authenticate;

    // Attach methods
    $auth->addMethod(new LocalMethod);
    $auth->addMethod(new MyMethod);

    $credential = new Credential;

    $credential->username = $username;
    $credential->password = $password;

    // Do authenticate
    $result = $auth->authenticate($credential);

    // False means login fail
    if (!$result)
    {
        // Print results to know what happened
        print_r($auth->getResults());

        throw new Exception('Username or password not matched');
    }

    $user = $auth->getCredential();

    return $user;
}
```

## Create Custom Methods

``` php
use Windwalker\Authenticate\Method\AbstractMethod;

class MyMethod extends AbstractMethod
{
    public function authenticate(Credential $credential)
    {
        $username = $credential->username;
        $password = $credential->password;

        if (!$username || !$password)
        {
            $this->status = Authenticate::EMPTY_CREDENTIAL;

            return false;
        }

        $user = Database::loadOne(array('username' => $username));

        if (!$user)
        {
            $this->status = Authenticate::USER_NOT_FOUND;

            return false;
        }

        if (!password_verify($password, $user->password))
        {
            $this->status = Authenticate::INVALID_CREDENTIAL;

            return false;
        }

        // Success
        $this->status = Authenticate::SUCCESS;

        // Set some data to Credential
        $credential->bind($user);

        unset($credential->password);

        return true;
    }
}
```
