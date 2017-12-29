# Attogram Router

* The Attogram Router is a small PHP router.
* No dependencies.
* One simple class.
* Composer ready:  `composer require attogram/router`

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

$base     = $router->getUriBase(); // Get the Base URI for this request:  http://example.com
$relative = $router->getUriRelative(); // Get the Relative URI for this request:  /test/foo/bar/
$vars     = $router->getVars(); // Get URI variables:  $vars = ['foo', 'bar', ...] or empty []

// Do something interesting...

```
