# Windwalker Authorisation

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/authorisation": "~3.0"
    }
}
```

## Create Authorisation and add policies

A simple example to use Closure as policy with action name `can.edit.article`.

``` php
use Windwalker\Authorisation\Authorisation;

$auth = new Authorisation;

$auth->addPolicy('can.edit.article', function (User $user, \stdClass $article)
{
    return $user->isAdmin() || $user->id == $article->author_id;
});

// Check access
$auth->authorise('can.edit.article', $user, $article); // boolean
```

## Use Authorisation to Make ACL system

We can also use `Authorisation` object as a ACL handler, see this example. We find `blog.article` actions from `acl_list`
table in database, and check the `can.edit` action greater then `1`, so it means this user (or group) has access
to edit all articles in blog.

``` php
$auth->addPolicy('can.edit', function (User $user, $assetName)
{
    $action = $db->prepare('SELECT access FROM acl_list WHERE action = :action AND asset = :asset AND group = :group')
        ->bind('action', 'can.edit')
        ->bind('asset', $assetName)
        ->bind('group', $user->group_id)
        ->execute()
        ->fetchObject();

    return $action >= 1;
});

// Can edit articles
$auth->authorise('can.edit', $user, 'blog.article'); // boolean

// Can edit article with id = 3
$auth->authorise('can.edit', $user, 'blog.article.3'); // boolean
```

> NOTE: This is just an simple example to show how ACL works, you must write your own rules to implements ACL system.

## Pre-defined Policy

We can define a policy by creating classes and implements the ``

``` php
class CanEditPolicy implements \Windwalker\Authorisation\PolicyInterface
{
	public function authorise($user, $data = null)
	{
		return $user->isAdmin() || $user->id == $data->author_id;
	}
}

$auth->addPolicy('can.edit', new CanEditPolicy);

// After PHP 5.5, you can simply use ::class to add class name
$auth->addPolicy('can.edit', CanEditPolicy::class);
```

## Register Multiple Policies

Use Policy Provider, we can define policies in a class that more easily to add many policies.

``` php
use Windwalker\Authorisation\AuthorisationInterface;
use Windwalker\Authorisation\PolicyProviderInterface;

class MyPolicyProvider implements PolicyProviderInterface
{
	public function register(AuthorisationInterface $auth)
	{
		$auth->addPolicy('can.create.article', function () { ... });
		$auth->addPolicy('can.edit.article', function () { ... });
		$auth->addPolicy('can.edit.own.article', function () { ... });
		$auth->addPolicy('can.delete.article', function () { ... });
	}
}

// Register policies
$auth->registerPolicyProvider(new MyPolicyProvider);
```
