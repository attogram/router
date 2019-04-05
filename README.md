# Attogram Router

Welcome to the Attogram Router 
for PHP 7 - Small, Flexible, One class, and Composer ready.

[![Attogram Router](https://raw.githubusercontent.com/attogram/attogram-docs/master/router/attogram.router.250.png)](https://github.com/attogram/router)

[![Maintainability](https://api.codeclimate.com/v1/badges/95f2868eeb1ed710b794/maintainability)](https://codeclimate.com/github/attogram/router/maintainability)
[![Build Status](https://travis-ci.org/attogram/router.svg?branch=master)](https://travis-ci.org/attogram/router)
[![Latest Stable Version](https://poser.pugx.org/attogram/router/v/stable)](https://packagist.org/packages/attogram/router)
[![Total Downloads](https://poser.pugx.org/attogram/router/downloads)](https://packagist.org/packages/attogram/router)

Composer: `composer require attogram/router`

Git: `git clone https://github.com/attogram/router.git`

Download: `https://github.com/attogram/router/archive/master.zip`

License: `MIT`

## Examples

* live demo: https://getitdaily.com/attogram-router/
* demo source: https://github.com/attogram/router/tree/master/examples

## Usage

Setup URL rewriting. For example with Apache `.htaccess`:
```
Options +FollowSymLinks
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

Create your `index.php`.  For example:
```php
use Attogram\Router\Router;

require_once('/path/to/vendor/autoload.php');

$router = new Router();

// Allow routes
$router->allow('/', 'home');
$router->allow('/foo/bar', 'foobar');
$router->allow('/pi', 3.141);
$router->allow('/hello', function () { print 'world'; });
$router->allow('/book/?/chapter/?', function (Router $router) { 
    $book = $router->getVars()[0];
    $chapter = $router->getVars()[1];
});

// Get the $control that matches the current request
$control = $router->match(); 

// If no match, $control is null
if (!$control) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

// Now dispatch based on $control, in whatever manner you wish 
```

## Functions

### allow
`public function allow(string $route, $control)`
* Allow and set a control for a route
* $route = a string with the URI list, forward-slash delimited
  * Exact routing:
    - Home:  '/'
    - Page:  '/foo/bar'
      - preceding and trailing slashes are optional, except for top level '/'
  * Variable routing:
    - use a question mark to denote a URI segment as a variable
    - variables are retrieved as an ordered array via: $router->getVars()
    - Examples:
      - '/id/?'
      - '/book/?/chapter/?'
      - '/foo/?/?/?'
* $control = anything you want, a string, a closure, an array, an object, an int, a float, whatever!
 
### match
`public function match()`
* Get the control for the current request, or null if no matching request

### getVars
`public function getVars(): array`
* Get URI segment variables: ['foo', 'bar', ...] or empty []

### getHome

### getCurrent

### setForceSlash
`public function setForceSlash(bool $forceSlash)`
* Sets the optional forcing of a trailing slash on all requests
* by default is false

### redirect
`public function redirect(string $url, int $httpResponseCode = 301)`
* Redirect to a new url and exit
* optionally set a response code (301 = permanent, 302 = moved)
### getGet
`public function getGet(string $name = '')`
* Get a global _GET variable, or empty string if not found

### getServer
`public function getServer(string $name = '')`
* Get a global _SERVER variable, or empty string if not found

----
