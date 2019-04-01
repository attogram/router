# Attogram Router

Welcome to the Attogram Router 
for PHP 7 - Small, Flexible, One class, and Composer ready.

[![Attogram Router](https://raw.githubusercontent.com/attogram/router/master/examples/attogram.router.250.png)](https://github.com/attogram/router)

[![Maintainability](https://api.codeclimate.com/v1/badges/95f2868eeb1ed710b794/maintainability)](https://codeclimate.com/github/attogram/router/maintainability)
[![Build Status](https://travis-ci.org/attogram/router.svg?branch=master)](https://travis-ci.org/attogram/router)
[![Latest Stable Version](https://poser.pugx.org/attogram/router/v/stable)](https://packagist.org/packages/attogram/router)
[![Total Downloads](https://poser.pugx.org/attogram/router/downloads)](https://packagist.org/packages/attogram/router)

Composer: `composer require attogram/router`

Git: `git clone https://github.com/attogram/router.git`

Download: `https://github.com/attogram/router/archive/master.zip`

License: `MIT`

## Examples

http://getitdaily.com/attogram-router/

## Usage

Setup URL rewriting, example `.htaccess`:
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

// Get an Attogram Router instance
$router = new Router();

// Allow your routes
//   $router->allow(route, control)
//      route   = a string with the URI list, forward-slash delimited
//                Exact routing example:  /foo/bar
//                Variable routing, use question mark:  /foo/?
//      control = anything you want, a string, a closure, an array, an object, whatever

$router->allow('/', 'home');
$router->allow('/about', 'about');
$router->allow('/view/?', 'view');
$router->allow('/edit/?', 'edit');

// Get the route that matches the current request
$control  = $router->match(); 

// If no match, $control is null
if (!$control) {
    // handle 404 Page Not Found
    exit;
}

// Now dispatch based on $control, in whatever manner you wish 

// And have some helper functions:

// Get Base URI: (empty string) or path with no trailing slash
$base = $router->getUriBase();

// Get Relative URI:  /foo/bar/  (always with preceding and trailing slash)
$relative = $router->getUriRelative(); 

// Get URI variables: ['foo', 'bar', ...] or empty []
$vars = $router->getVars(); 

```
