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
        self::assertTrue(class_exists('Attogram\Router\Router'));
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
        $this->assertVarsAreNull($router);
    }

    public function testExactMatchFound()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/exact/match/';
        $router = new Router();
        $router->allow('/exact/match', 'passed');
        self::assertEquals('passed', $router->match());
        $this->assertVarsAreNull($router);
    }

    public function testExactMatchNotFound()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/not/found';
        $router = new Router();
        $router->allow('/exact/match', 'failed');
        self::assertEquals(null, $router->match());
        $this->assertVarsAreNull($router);
    }

    public function testExactMatchLastFound()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/exact/';
        $router = new Router();
        $router->allow('/a', 'failed');
        $router->allow('/b', 'failed');
        $router->allow('/c', 'failed');
        $router->allow('/d', 'failed');
        $router->allow('/e', 'failed');
        $router->allow('/f', 'failed');
        $router->allow('/g', 'failed');
        $router->allow('/h', 'failed');
        $router->allow('/i', 'failed');
        $router->allow('/j', 'failed');
        $router->allow('/ex', 'failed');
        $router->allow('/exa', 'failed');
        $router->allow('/exac', 'failed');
        $router->allow('/exactl', 'failed');
        $router->allow('/exactly', 'failed');
        $router->allow('/exact', 'passed');
        self::assertEquals('passed', $router->match());
        $this->assertVarsAreNull($router);
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
        $this->assertVarsAreNull($router, 1);
    }

    public function testVariableMatchFoundTwo()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/variable/foo/bar';
        $router = new Router();
        $router->allow('/variable/?/?', 'passed');
        self::assertEquals('passed', $router->match());
        self::assertEquals('foo', $router->getVar());
        self::assertEquals('foo', $router->getVar(0));
        self::assertEquals('bar', $router->getVar(1));
        $this->assertVarsAreNull($router, 2);
    }

    public function testVariableMatchFoundThree()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/variable/foo/bar/alpha';
        $router = new Router();
        $router->allow('/variable/?/?/?', 'passed');
        self::assertEquals('passed', $router->match());
        self::assertEquals('foo', $router->getVar());
        self::assertEquals('foo', $router->getVar(0));
        self::assertEquals('bar', $router->getVar(1));
        self::assertEquals('alpha', $router->getVar(2));
        $this->assertVarsAreNull($router, 3);
    }

    public function testVariableMatchFoundFour()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/variable/foo/bar/alpha/beta';
        $router = new Router();
        $router->allow('/variable/?/?/?/?', 'passed');
        self::assertEquals('passed', $router->match());
        self::assertEquals('foo', $router->getVar());
        self::assertEquals('foo', $router->getVar(0));
        self::assertEquals('bar', $router->getVar(1));
        self::assertEquals('alpha', $router->getVar(2));
        self::assertEquals('beta', $router->getVar(3));
        $this->assertVarsAreNull($router, 4);
    }

    public function testVariableMatchFoundFive()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/variable/foo/bar/alpha/beta/omega';
        $router = new Router();
        $router->allow('/variable/?/?/?/?/?', 'passed');
        self::assertEquals('passed', $router->match());
        self::assertEquals('foo', $router->getVar());
        self::assertEquals('foo', $router->getVar(0));
        self::assertEquals('bar', $router->getVar(1));
        self::assertEquals('alpha', $router->getVar(2));
        self::assertEquals('beta', $router->getVar(3));
        self::assertEquals('omega', $router->getVar(4));
        $this->assertVarsAreNull($router, 5);
    }

    public function testVariableMatchLastFound()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/variable/foo/';
        $router = new Router();
        $router->allow('/a/?', 'failed');
        $router->allow('/b/?', 'failed');
        $router->allow('/c/?', 'failed');
        $router->allow('/d/?', 'failed');
        $router->allow('/e/?', 'failed');
        $router->allow('/f/?', 'failed');
        $router->allow('/g/?', 'failed');
        $router->allow('/h/?', 'failed');
        $router->allow('/i/?', 'failed');
        $router->allow('/j/?', 'failed');
        $router->allow('/v/?', 'failed');
        $router->allow('/va/?', 'failed');
        $router->allow('/var/?', 'failed');
        $router->allow('/vari/?', 'failed');
        $router->allow('/varia/?', 'failed');
        $router->allow('/variab/?', 'failed');
        $router->allow('/variabl/?', 'failed');
        $router->allow('/variables/?', 'failed');
        $router->allow('/variable/?', 'passed');
        self::assertEquals('passed', $router->match());
        self::assertEquals('foo', $router->getVar());
        self::assertEquals('foo', $router->getVar(0));
        $this->assertVarsAreNull($router, 1);
    }

    public function testVariableMatchNotFound()
    {
        $this->localSetUp();
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/not/found';
        $router = new Router();
        $router->allow('/variable/?', 'failed');
        $this->assertVarsAreNull($router);
    }

    /**
     * @param Router $router
     * @param int $nullsFromIndex
     */
    public function assertVarsAreNull(Router $router, int $nullsFromIndex = 0)
    {
        for ($index = 0; $index < 100; $index++) {
            if ($index < $nullsFromIndex) {
                continue;
            }
            if ($index === 0) {
                self::assertEquals(null, $router->getVar());
            }
            self::assertEquals(null, $router->getVar($index));
        }
    }

    public function testGetProtocolHttpsOn443() {
        $this->localSetUp();
        $GLOBALS['_SERVER']['HTTPS'] = 'on';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 443;
        $router = new Router();
        self::assertEquals('https', $router->getProtocol());
    }

    public function testGetProtocolHttps443() {
        $this->localSetUp();
        unset($GLOBALS['_SERVER']['HTTPS']);
        $GLOBALS['_SERVER']['SERVER_PORT'] = 443;
        $router = new Router();
        self::assertEquals('https', $router->getProtocol());
    }

    public function testGetProtocolHttp80() {
        $this->localSetUp();
        unset($GLOBALS['_SERVER']['HTTPS']);
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $router = new Router();
        self::assertEquals('http', $router->getProtocol());
    }

    public function testGetProtocolHttpsOff80() {
        $this->localSetUp();
        $GLOBALS['_SERVER']['HTTPS'] = 'off';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $router = new Router();
        self::assertEquals('http', $router->getProtocol());
    }

    public function testGetProtocolHttp8080() {
        $this->localSetUp();
        unset($GLOBALS['_SERVER']['HTTPS']);
        $GLOBALS['_SERVER']['SERVER_PORT'] = 8080;
        $router = new Router();
        self::assertEquals('http', $router->getProtocol());
    }

    public function testGetProtocolHttps8080() {
        $this->localSetUp();
        $GLOBALS['_SERVER']['HTTPS'] = 'on';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 8080;
        $router = new Router();
        self::assertEquals('https', $router->getProtocol());
    }

    public function testGetHost80() {
        $this->localSetUp();
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $router = new Router();
        self::assertEquals('foo.bar', $router->getHost());
    }

    public function testGetHostFull80() {
        $this->localSetUp();
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        unset($GLOBALS['_SERVER']['HTTPS']);
        $router = new Router();
        self::assertEquals('http://foo.bar', $router->getHostFull());
    }

    public function testGetHost443() {
        $this->localSetUp();
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'secure.foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 443;
        $router = new Router();
        self::assertEquals('secure.foo.bar', $router->getHost());
    }

    public function testGetHostFull443() {
        $this->localSetUp();
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'secure.foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 443;
        unset($GLOBALS['_SERVER']['HTTPS']);
        $router = new Router();
        self::assertEquals('https://secure.foo.bar', $router->getHostFull());
    }

    public function testGetHostFullHttpsOff() {
        $this->localSetUp();
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'secure.foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $GLOBALS['_SERVER']['HTTPS'] = 'off';
        $router = new Router();
        self::assertEquals('http://secure.foo.bar', $router->getHostFull());
    }

    public function testGetHost8080() {
        $this->localSetUp();
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 8080;
        $router = new Router();
        self::assertEquals('foo.bar:8080', $router->getHost());
    }

    public function testGetHostFull8080() {
        $this->localSetUp();
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 8080;
        $router = new Router();
        self::assertEquals('http://foo.bar:8080', $router->getHostFull());
    }

    public function testGetHostFull8080HttpsOn() {
        $this->localSetUp();
        $GLOBALS['_SERVER']['HTTPS'] = 'on';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 8080;
        $router = new Router();
        self::assertEquals('https://foo.bar:8080', $router->getHostFull());
    }

    public function testGetGet() {
        $this->localSetUp();
        $GLOBALS['_GET']['foo'] = 'bar';
        $router = new Router();
        self::assertEquals($_GET, $router->getGet());
        self::assertEquals('bar', $router->getGet('foo'));
        self::assertEquals('', $router->getGet('not.foo'));
    }

    public function testGetServer() {
        $this->localSetUp();
        $router = new Router();
        self::assertEquals($_SERVER, $router->getServer());
        self::assertEquals($_SERVER['REQUEST_URI'], $router->getServer('REQUEST_URI'));
        self::assertEquals($_SERVER['SCRIPT_NAME'], $router->getServer('SCRIPT_NAME'));
        self::assertEquals($_SERVER['SERVER_NAME'], $router->getServer('SERVER_NAME'));
        self::assertEquals($_SERVER['HTTPS'], $router->getServer('HTTPS'));
        self::assertEquals($_SERVER['SERVER_PORT'], $router->getServer('SERVER_PORT'));
        self::assertEquals(
            isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '',
            $router->getServer('QUERY_STRING')
        );
        $GLOBALS['_SERVER']['QUERY_STRING'] = 'a=b';
        self::assertEquals($_SERVER['QUERY_STRING'], $router->getServer('QUERY_STRING'));
        self::assertEquals('', $router->getServer('NOT_FOUND'));
        unset($GLOBALS['_SERVER']['QUERY_STRING']);
    }


//    public function testGetHome() {}
//    public function testGetHomeFull() {}
//    public function testGetCurrent() {}
//    public function testGetCurrentFull() {}

    /**
     * @runInSeparateProcess
     */
    public function testRedirect() {
        $this->localSetUp();
        $router = new Router();
        $router->redirect('/redirected', 301);
        self::assertTrue(true);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRedirectNoExit() {
        $this->localSetUp();
        $router = new Router();
        $router->redirect('/redirected', 301, false);
        self::assertTrue(true);
    }
}
