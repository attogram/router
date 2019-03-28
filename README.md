# Attogram Router

Welcome to the Attogram Router for PHP 7:
Small, Flexible, No dependencies, One class, and Composer ready.

`composer require attogram/router`

[![Maintainability](https://api.codeclimate.com/v1/badges/95f2868eeb1ed710b794/maintainability)](https://codeclimate.com/github/attogram/router/maintainability)
[![Build Status](https://travis-ci.org/attogram/router.svg?branch=master)](https://travis-ci.org/attogram/router)
[![Latest Stable Version](https://poser.pugx.org/attogram/router/v/stable)](https://packagist.org/packages/attogram/router)
[![Latest Unstable Version](https://poser.pugx.org/attogram/router/v/unstable)](https://packagist.org/packages/attogram/router)
[![Total Downloads](https://poser.pugx.org/attogram/router/downloads)](https://packagist.org/packages/attogram/router)


## Example usage:

Setup URL rewriting, example `.htaccess`:
```
Options +FollowSymLinks
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

Create your site, example `index.php`:
```php
use Attogram\Router\Router;

require_once('/path/to/vendor/autoload.php');

$router = new Router();

// Set Exact Routes:
$router->allow('/',                'ControllerHome');
$router->allow('/test/',           'ControllerTest1');
$router->allow('/test/test/',      'ControllerTest2');
$router->allow('/test/test/test/', 'ControllerTest3');

// Set Variable Routes:
$router->allow('/test/?/',         'ControllerTestVariable1');
$router->allow('/test/?/?/',       'ControllerTestVariable2');
$router->allow('/test/?/?/?/',     'ControllerTestVariable3');
$router->allow('/test/?/test/',    'ControllerTestVariableTest1');
$router->allow('/test/?/test/?/',  'ControllerTestVariableTest2');

$control  = $router->match(); // Get the controller name for this request

if (!$control) {
    // handle 404 Page Not Found
}

$base     = $router->getUriBase(); // Base URI:  (empty string) or path with no trailing slash
$relative = $router->getUriRelative(); // Relative URI:  /test/foo/bar/  (always with preceding and trailing slash)
$vars     = $router->getVars(); // Get URI variables:  $vars = ['foo', 'bar', ...] or empty []

// Now do something interesting...

```
