<?php
declare(strict_types = 1);

use Attogram\Router\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function localSetUp()
    {
        parent::setUp();
        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['_SERVER']['SCRIPT_NAME'] = '/index.php';
    }

    public function testConstruct()
    {
        $this->localSetUp();
        $router = new Router();
        self::assertInstanceOf(Router::class, $router);
        self::assertClassHasAttribute('control', Router::class);
        self::assertClassHasAttribute('forceSlash', Router::class);
        self::assertClassHasAttribute('routesExact', Router::class);
        self::assertClassHasAttribute('routesVariable', Router::class);
        self::assertClassHasAttribute('uri', Router::class);
        self::assertClassHasAttribute('uriBase', Router::class);
        self::assertClassHasAttribute('uriCount', Router::class);
        self::assertClassHasAttribute('uriRelative', Router::class);
        self::assertClassHasAttribute('vars', Router::class);
        self::assertTrue(method_exists($router, 'allow'));
        self::assertTrue(method_exists($router, 'getCurrent'));
        self::assertTrue(method_exists($router, 'getCurrentFull'));
        self::assertTrue(method_exists($router, 'getGet'));
        self::assertTrue(method_exists($router, 'getHome'));
        self::assertTrue(method_exists($router, 'getHomeFull'));
        self::assertTrue(method_exists($router, 'getHost'));
        self::assertTrue(method_exists($router, 'getHostFull'));
        self::assertTrue(method_exists($router, 'getProtocol'));
        self::assertTrue(method_exists($router, 'getServer'));
        self::assertTrue(method_exists($router, 'getVar'));
        self::assertTrue(method_exists($router, 'match'));
        self::assertTrue(method_exists($router, 'redirect'));
        self::assertTrue(method_exists($router, 'setForceSlash'));
    }

    public function testSemanticVersion()
    {
        $this->localSetUp();
        self::assertEquals(true, is_string(Router::VERSION));
        self::assertGreaterThanOrEqual(
            1,
            preg_match(
                '/^'
                . '(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)'
                . '(-(0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)'
                . '(\.(0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*)'
                . '?(\+[0-9a-zA-Z-]+(\.[0-9a-zA-Z-]+)*)?'
                . '$/',
                Router::VERSION
            ),
            'Invalid Semantic Version: ' . Router::VERSION
        );
    }

    public function testMatchNoRoutes()
    {
        $this->localSetUp();
        $router = new Router();
        self::assertEquals(null, $router->match());
    }

    public function testExactMatchFound()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/exact/match/';
        $router = new Router();
        $router->allow('/exact/match', 'passed');
        self::assertEquals('passed', $router->match());
    }

    public function testExactMatchNotFound()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/not/found';
        $router = new Router();
        $router->allow('/exact/match', 'failed');
        self::assertEquals(null, $router->match());
    }

    public function testVariableMatchFound()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/variable/foo';
        $router = new Router();
        $router->allow('/variable/?', 'passed');
        self::assertEquals('passed', $router->match());
        self::assertEquals('foo', $router->getVar());
        self::assertEquals('foo', $router->getVar(0));
        self::assertEquals(null, $router->getVar(1));
        self::assertEquals(null, $router->getVar(2));
        self::assertEquals(null, $router->getVar(3));
        self::assertEquals(null, $router->getVar(4));
        self::assertEquals(null, $router->getVar(5));
    }

    public function testVariableMatchNotFound()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/not/found';
        $router = new Router();
        $router->allow('/variable/?', 'failed');
        self::assertEquals(null, $router->match());
        self::assertEquals(null, $router->getVar());
        self::assertEquals(null, $router->getVar(0));
        self::assertEquals(null, $router->getVar(1));
        self::assertEquals(null, $router->getVar(2));
        self::assertEquals(null, $router->getVar(3));
        self::assertEquals(null, $router->getVar(4));
        self::assertEquals(null, $router->getVar(5));
    }
}
