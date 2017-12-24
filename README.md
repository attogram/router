# Attogram Router

The Attogram Router is a small PHP router. 

## Example usage:

```php
use Attogram\Router\Router;

require_once('../vendor/autoload.php');

$router = new Router();

// Set Exact Routes:
$router->allow('/',                     'ControllerHome');
$router->allow('/test/',                'ControllerTest1');
$router->allow('/test/test/',           'ControllerTest2');
$router->allow('/test/test/test/',      'ControllerTest3');
$router->allow('/test/test/test/test/', 'ControllerTest4');

// Set Variable Routes:
$router->allow('/test/?/',              'ControllerTestVariable1');
$router->allow('/test/?/?/',            'ControllerTestVariable2');
$router->allow('/test/?/?/?/',          'ControllerTestVariable3');
$router->allow('/test/?/test/',         'ControllerTestVariableTest1');
$router->allow('/test/?/test/?/',       'ControllerTestVariableTest2');

$control  = $router->match(); // Get the controller name for this request

if (!$control) {
	// handle 404 Not Found
}

$base     = $router->getUriBase(); // Get the Base URI for this request:  http://example.com
$relative = $router->getUriRelative(); // Get the Relative URI for this request:  /foo/bar/
$full     = $base . $relative; // Make the full URI:  http://example.com/foo/bar
$vars     = $router->getVars(); // Get values from a variable route, as an array indexed to URI order

// Do something interesting...

```
