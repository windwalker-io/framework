# Windwalker Http

This package provides PSR7 standard Http message objects, Uri objects, Stream objects and Client request object.

> Some parts of this package based on [phly/http](https://github.com/phly/http) and [joomla/http](https://github.com/joomla-framework/http)

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/http": "~3.0"
    }
}
```

## Make A Request

HttpClient is a simple class to make restful request.

``` php
use Windwalker\Http\HttpClient;

$http = new HttpClient;

$response = $http->get('http://example.com/?foo=bar');

// This is PSR7 ResponseInterface
(string) $response->getBody();
```

### Other Methods

``` php
$http = new HttpClient;

// The post data can be query string or array
$response = $http->post('http://example.com/?foo=bar', array('post_data' => 'data'));
$response = $http->put('http://example.com/?foo=bar', array('post_data' => 'data'));
$response = $http->patch('http://example.com/?foo=bar', array('post_data' => 'data'));
$response = $http->delete('http://example.com/?foo=bar', array('post_data' => 'data'));

$response = $http->head('http://example.com/?foo=bar');
$response = $http->trace('http://example.com/?foo=bar');
$response = $http->options('http://example.com/?foo=bar');

// With headers
$response = $http->get('http://example.com/', null, array('X-Foo' => 'Bar'));

// Use request()
$response = $http->request('POST', 'http://example.com/?foo=bar', 'this=is&post=data');
```

### Use Psr RequestInterface to Make Request

Psr7 Request is a immutable object, you have to get the return object every operation.

``` php
use Windwalker\Http\Request;

$request = new Request;

// Note: You have to get the return value.
// Every change will return new object.
$request = $request->withRequestTarget('http://example.com/flower/sakura')
    ->withMethod('POST')
    ->withAddedHeader('Authorization', 'Bearer ' . $token)
    ->withAddedHeader('Content-Type', 'application/text');

// OR
$request = new Request(
    'http://example.com/flower/sakura',
    'POST',
    'php://memory',
    array(
        'Authorization' => 'Bearer ' . $token,
        'Content-Type'  => 'application/json',
    )
);

// This is a POST request so we write post data to body
$request->getBody()->write('this=is&post=data');

$http = new HttpClient;

// Send request
$response = $http->send($request);
```

Use Uri and Json output.

``` php
use Windwalker\Http\Request;
use Windwalker\Uri\PsrUri;

$request = (new Request)
    ->withUri(new PsrUri('http://example.com'))
    ->withMethod('POST')
    ->withAddedHeader('Authorization', 'Bearer ' . $token)
    ->withAddedHeader('Content-Type', 'application/json') // Use JSON
    
    // Note: Request will ignore path and query in Uri
    // So we have to set RequestTarget here
    ->withRequestTarget('/path/of/uri?flower=sakura');

// If you want to set a non-origin-form request target, set the
// request-target explicitly:
$request = $request->withRequestTarget((string) $uri);       // absolute-form
$request = $request->withRequestTarget($uri->getAuthority(); // authority-form
$request = $request->withRequestTarget('*');                 // asterisk-form

// This is JSON request so we encode data here
$request->getBody()->write(json_encode($data));
$response = $http->send($request);

$response->getStatusCode(); // 200 is OK
```

### Custom Transports and Options

Now support Curl and Steam 2 transports.

``` php
use Windwalker\Http\Transport\CurlTransport;

$options = array(
    'certpath' => '/custom/cert.pem'
);

$transport = new CurlTransport($options);

// Set transport when client new
$http = new HttpClient(array(), $transport);
```

Set custom CURL options:

``` php
$options = array(
    'options' => array(
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => true
    )
);

$httpOptions = array(
    'headers' => array(
        'X-Foo' => 'Bar'
    )
);

$http = new HttpClient($httpOptions, new CurlTransport($options));
```

### Download Remote File
 
``` php
$http = new HttpClient;

$dest = '/path/to/local/file.zip';

$response = $http->download('http://example.com/file.zip', $dest);

if ($response->getStatusCode() != 200)
{
    // Error
}
```

### Response Interface

Response object holds a Stream object to store returned string.

``` php
// The return value is: 'FOO BAR'
$body = $response->getBody();

// Simply to string
(string) $body; // FOO BAR

$body->seek(2);
$body->getContents(); // O BAR

$body->rewind();
$body->read(5); // FOO B

$body->getSize(); // 7
```

## Uri

`Uri` is a simple Uri object to modify URL but not Psr UriInterface.

The methods provided in the `Uri` class allow you to manipulate all aspects of a uri. For example, suppose you wanted to set a new uri, add in a port, and then also post a username and password to authenticate a .htaccess security file. You could use the following syntax:

``` php
// new uri object
$uri = new Windwalker\Uri\Uri;

$uri->setHost('http://localhost');
$uri->setPort('8888');
$uri->setUser('myUser');
$uri->setPass('myPass');

echo $uri->__toString();
```

This will output:

```
myUser:myPass@http://localhost:8888
```

If you wanted to add a specific filepath after the host you could use the `setPath()` method:

``` php
// set path
$uri->setPath('path/to/file.php');
```

Which will output

```
myUser:myPass@http://localhost:8888path/to/file.php
```

Adding a URL query:

``` php
// url query
$uri->setQuery('foo=bar');
```

Output:

```
myUser:myPass@http://localhost:8888path/to/file.php?foo=bar
```

### PsrUri

`PsrUri` is a Uri object implemented the Psr UriInterface.

This object is also immutable, so we must get return value as new object every change.

``` php
$uri = (new PsrUri('http://example.com'))
    ->withScheme('https')
    ->withUserInfo('user', 'pass')
    ->withPath('/path/to/target')
    ->withQuery('flower=sakura')
    ->withFragment('#hash');
    
(string) $uri; // https://user:pass@example.com/path/to/target?flower=sakura#fragment
```

## Stream

Stream is a powerful stream wrapper.

Read write data to memory:

``` php
$stream = new Stream('php://memory', 'wb+');

$stream->write('Foo Bar');

$stream->rewind(); // Back to begin

// Now we take something we wrote into memory

$stream->__toString(); // get: Foo Bar

// OR

$stream->rewind();
$stream->getContents(); // get: Foo Bar
```

Read data from `php://input`

``` php
$stream = new PhpInputSteam;

$data = $stream->__toString(); // foo=bar

$query = \Windwalker\Uri\UriHelper::parseQuery($data); // array('foo' => 'bar')
```

Read file:

``` php
$stream = new Stream('/path/to/file.txt', 'r+');

$stream->__toString(); // Read

$steam->seek($stream->getSize());
$steam->write('new string'); // Write
```

Quick copy stream.

``` php
// Remote source
$src = new Stream('http://example/test.txt');

// Local store
$dest = new Stream('/path/to/local/test.txt');

// Do copy
\Windwalker\Http\Helper\StreamHelper::copy($src, $dest);

// Get Data
$dest->rewind();
$data = $dest->getContents();
```

See: [Psr7 StreamInterface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md#13-streams)
/ [API](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md#34-psrhttpmessagestreaminterface)

## Http Server

### `ServerRequest`

A Request object to store server information, like: `$_SERVER`, `$_COOKIE`, `$_REQUEST` etc.

``` php
use Windwalker\Http\Request\ServerRequestFactory;

$request = ServerRequestFactory::createFromGlobals();

// Or create by suprtglobals
$request = ServerRequestFactory::createFromGlobals(array(
	$_SERVER,
	$_GET,
	$_POST,
	$_COOKIE,
	$_FILES
));

// Get Uri with URL information
$request->getUri();
```

### Create A Http Server

``` php
$handler = function (Request $request, Response $response, $next = null)
{
	$response->getBody()->write('Hello World~~~!');

	return $response;
};

$server = WebHttpServer::create($handler, ServerRequestFactory::createFromGlobals());

$server->listen();
```

### Error Handler

Add a callable to `listen()` as error handler.

``` php
$handler = function (Request $request, Response $response, callable $error = null)
{
	try
	{
		$response->getBody()->write('Hello World~~~!');
	}
	catch (\Exception $e)
	{
		return $error($e, $request, $response);
	}

	return $response;
};

$server = WebHttpServer::create($handler, ServerRequestFactory::createFromGlobals());

$server->listen(function (Exception $e, Request $request, Response $response)
{
	// Render error page
});
```

### `UploadedFile`

An object to store uploaded files, see: [Uploaded files interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md#16-uploaded-files)

``` php
$files = array();

foreach ($_FILES as $name => $file)
{
    $files[$name] = new UploadedFile($file['tmp_name'], $file['size'], $file['error'], $file['name'], $file['type']);
}

$request = new ServerRequest(
  $_SERVER,
  $_GET,
  $_POST,
  $_COOKIE,
  $files
);
```

The ServerRequestFactory object will help us organize `$_FILES` data to `UploadedFile`.

``` php
$request = ServerRequestFactory::createFromGlobals();
$files = $request->getUploadedFiles();

if (!$files['foo']->getError())
{
	$files['foo']->moveTo('/tmp/files/' . $files['foo']->getClientFilename());
}
```

## Responses

Windwalker Http provides a set of formatted Responses to return data with different formats.

``` php
$response = new \Windwalker\Http\Response\HtmlResponse('<html> ... </html>');
$response = new \Windwalker\Http\Response\JsonResponse(array('foo' => 'bar'), 200, $headers);
$response = new \Windwalker\Http\Response\XmlResponse(new \SimpleXMLElement('<root />'), 200, $headers);
$response = new \Windwalker\Http\Response\RedirectResponse($url, 301);
```

## More About Psr 7

[PSR7 HTTP message interfaces](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md) / 
[HTTP Message Meta Document](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message-meta.md)
