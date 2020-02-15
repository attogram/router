<?php
declare(strict_types = 1);

use Attogram\Router\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function getRouter()
    {
        parent::setUp();
        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['_SERVER']['SCRIPT_NAME'] = '/index.php';
        self::assertTrue(class_exists('Attogram\Router\Router'));
        return new Router();
    }

    public function testConstruct()
    {
        $router = $this->getRouter();
        self::assertClassHasAttribute('control', Router::class);
        self::assertClassHasAttribute('forceSlash', Router::class);
        self::assertClassHasAttribute('routesExact', Router::class);
        self::assertClassHasAttribute('routesVariable', Router::class);
        self::assertClassHasAttribute('uri', Router::class);
        self::assertClassHasAttribute('uriBase', Router::class);
        self::assertClassHasAttribute('uriCount', Router::class);
        self::assertClassHasAttribute('uriRelative', Router::class);
        self::assertClassHasAttribute('vars', Router::class);
        self::assertInstanceOf(Router::class, $router);
        self::assertTrue(method_exists($router, 'allow'));
        self::assertNull($router->allow('/empty/string', ''));
        self::assertNull($router->allow('/null', null));
        self::assertNull($router->allow('/this', $this));
        self::assertNull($router->allow('/0', (int) 0));
        self::assertNull($router->allow('/a', (int) 1));
        self::assertNull($router->allow('/b', (string) 'one'));
        self::assertNull($router->allow('/c', (array) ['one']));
        self::assertNull($router->allow('/d', (float) 3.141));
        self::assertNull($router->allow('/e', (object) new stdClass()));
        self::assertNull($router->allow('/f', function () { echo 'one'; }));
        self::assertNull($router->allow('/g', (bool) true));
        self::assertNull($router->allow('/h', (bool) false));
        self::assertTrue(method_exists($router, 'match'));
        self::assertNull($router->match());
        self::assertTrue(method_exists($router, 'getCurrent'));
        self::assertTrue(is_string($router->getCurrent()));
        self::assertTrue(method_exists($router, 'getCurrentFull'));
        self::assertTrue(is_string($router->getCurrentFull()));
        self::assertTrue(method_exists($router, 'getGet'));
        self::assertTrue(is_array($router->getGet()));
        self::assertEmpty($router->getGet('foobar'));
        self::assertTrue(method_exists($router, 'getHome'));
        self::assertTrue(is_string($router->getHome()));
        self::assertTrue(method_exists($router, 'getHomeFull'));
        self::assertTrue(is_string($router->getHomeFull()));
        self::assertTrue(method_exists($router, 'getHost'));
        self::assertTrue(is_string($router->getHost()));
        self::assertTrue(method_exists($router, 'getHostFull'));
        self::assertTrue(is_string($router->getHostFull()));
        self::assertTrue(method_exists($router, 'getProtocol'));
        self::assertTrue(is_string($router->getProtocol()));
        self::assertTrue(in_array($router->getProtocol(), ['http', 'https']));
        self::assertTrue(method_exists($router, 'getServer'));
        self::assertTrue(is_array($router->getServer()));
        self::assertEmpty($router->getServer('FOOBAR'));
        self::assertTrue(method_exists($router, 'getVar'));
        $this->assertVarsAreNull($router);
        self::assertTrue(method_exists($router, 'redirect'));
        self::assertTrue(method_exists($router, 'setForceSlash'));
        self::assertNull($router->setForceSlash(true));
        self::assertNull($router->setForceSlash(false));
    }

    public function testSemanticVersion()
    {
        self::assertTrue(is_string(Router::VERSION));
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
        $router = $this->getRouter();
        self::assertNull($router->match());
        $this->assertVarsAreNull($router);
    }

    public function testExactMatchFound()
    {
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/exact/match/';
        $router = $this->getRouter();
        $router->allow('/exact/match', 'passed');
        self::assertEquals('passed', $router->match());
        $this->assertVarsAreNull($router);
    }

    public function testExactMatchNotFound()
    {
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/not/found';
        $router = $this->getRouter();
        $router->allow('/exact/match', 'failed');
        self::assertNull($router->match());
        $this->assertVarsAreNull($router);
    }

    public function testExactMatchLastFound()
    {
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/exact/';
        $router = $this->getRouter();
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
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/variable/foo';
        $router = $this->getRouter();
        $router->allow('/variable/?', 'passed');
        self::assertEquals('passed', $router->match());
        self::assertEquals('foo', $router->getVar());
        self::assertEquals('foo', $router->getVar(0));
        $this->assertVarsAreNull($router, 1);
    }

    public function testVariableMatchFoundTwo()
    {
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/variable/foo/bar';
        $router = $this->getRouter();
        $router->allow('/variable/?/?', 'passed');
        self::assertEquals('passed', $router->match());
        self::assertEquals('foo', $router->getVar());
        self::assertEquals('foo', $router->getVar(0));
        self::assertEquals('bar', $router->getVar(1));
        $this->assertVarsAreNull($router, 2);
    }

    public function testVariableMatchFoundThree()
    {
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/variable/foo/bar/alpha';
        $router = $this->getRouter();
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
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/variable/foo/bar/alpha/beta';
        $router = $this->getRouter();
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
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/variable/foo/bar/alpha/beta/omega';
        $router = $this->getRouter();
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
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/variable/foo/';
        $router = $this->getRouter();
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
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/not/found';
        $router = $this->getRouter();
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
                self::assertNull($router->getVar());
            }
            self::assertNull($router->getVar($index));
        }
    }

    public function testGetProtocolHttpsOn443()
    {
        $GLOBALS['_SERVER']['HTTPS'] = 'on';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 443;
        $router = $this->getRouter();
        self::assertEquals('https', $router->getProtocol());
    }

    public function testGetProtocolHttps443()
    {
        unset($GLOBALS['_SERVER']['HTTPS']);
        $GLOBALS['_SERVER']['SERVER_PORT'] = 443;
        $router = $this->getRouter();
        self::assertEquals('https', $router->getProtocol());
    }

    public function testGetProtocolHttp80()
    {
        unset($GLOBALS['_SERVER']['HTTPS']);
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $router = $this->getRouter();
        self::assertEquals('http', $router->getProtocol());
    }

    public function testGetProtocolHttpsOff80()
    {
        $GLOBALS['_SERVER']['HTTPS'] = 'off';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $router = $this->getRouter();
        self::assertEquals('http', $router->getProtocol());
    }

    public function testGetProtocolHttp8080()
    {
        unset($GLOBALS['_SERVER']['HTTPS']);
        $GLOBALS['_SERVER']['SERVER_PORT'] = 8080;
        $router = $this->getRouter();
        self::assertEquals('http', $router->getProtocol());
    }

    public function testGetProtocolHttps8080()
    {
        $GLOBALS['_SERVER']['HTTPS'] = 'on';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 8080;
        $router = $this->getRouter();
        self::assertEquals('https', $router->getProtocol());
    }

    public function testGetHost80()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $router = $this->getRouter();
        self::assertEquals('foo.bar', $router->getHost());
    }

    public function testGetHostFull80()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        unset($GLOBALS['_SERVER']['HTTPS']);
        $router = $this->getRouter();
        self::assertEquals('http://foo.bar', $router->getHostFull());
    }

    public function testGetHost443()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'secure.foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 443;
        $router = $this->getRouter();
        self::assertEquals('secure.foo.bar', $router->getHost());
    }

    public function testGetHostFull443()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'secure.foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 443;
        unset($GLOBALS['_SERVER']['HTTPS']);
        $router = $this->getRouter();
        self::assertEquals('https://secure.foo.bar', $router->getHostFull());
    }

    public function testGetHostFullHttpsOff()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'secure.foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $GLOBALS['_SERVER']['HTTPS'] = 'off';
        $router = $this->getRouter();
        self::assertEquals('http://secure.foo.bar', $router->getHostFull());
    }

    public function testGetHost8080()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 8080;
        $router = $this->getRouter();
        self::assertEquals('foo.bar:8080', $router->getHost());
    }

    public function testGetHostFull8080()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 8080;
        $router = $this->getRouter();
        self::assertEquals('http://foo.bar:8080', $router->getHostFull());
    }

    public function testGetHostFull8080HttpsOn()
    {
        $GLOBALS['_SERVER']['HTTPS'] = 'on';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 8080;
        $router = $this->getRouter();
        self::assertEquals('https://foo.bar:8080', $router->getHostFull());
    }

    public function testGetGet()
    {
        $GLOBALS['_GET']['foo'] = 'bar';
        $router = $this->getRouter();
        self::assertEquals($_GET, $router->getGet());
        self::assertEquals('bar', $router->getGet('foo'));
        self::assertNull($router->getGet('not.foo'));
    }

    public function testGetPost()
    {
        $GLOBALS['_POST']['foo'] = 'bar';
        $router = $this->getRouter();
        self::assertEquals($_POST, $router->getPost());
        self::assertEquals('bar', $router->getPost('foo'));
        self::assertNull($router->getPost('not.foo'));
    }

    public function testGetServer()
    {
        $router = $this->getRouter();
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
        unset($GLOBALS['_SERVER']['QUERY_STRING']);
        self::assertNull($router->getServer('NOT_FOUND'));

    }

    /**
     * @runInSeparateProcess
     */
    // public function testRedirect()
    // {
    //     $router = $this->getRouter();
    //     $router->redirect('/redirected', 301);
    //     self::fail(); // should have exited already
    // }

    /**
     * @runInSeparateProcess
     */
    public function testRedirectNoExit()
    {
        $router = $this->getRouter();
        $router->redirect('/redirected', 301, false);
        self::assertTrue(true);
    }

    public function testGetHome()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $GLOBALS['_SERVER']['HTTPS'] = 'off';
        $router = $this->getRouter();
        self::assertEquals('/', $router->getHome());
    }

    public function testGetHomeFull()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $GLOBALS['_SERVER']['HTTPS'] = 'off';
        $router = $this->getRouter();
        self::assertEquals('http://foo.bar/', $router->getHomeFull());
    }

    public function testGetCurrent()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $GLOBALS['_SERVER']['HTTPS'] = 'off';
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/current/url';
        $router = $this->getRouter();
        self::assertEquals('/current/url', $router->getCurrent());
    }

    public function testGetCurrentSlash()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $GLOBALS['_SERVER']['HTTPS'] = 'off';
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/current/url/';
        $router = $this->getRouter();
        self::assertEquals('/current/url/', $router->getCurrent());
    }

    public function testGetCurrentFull()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $GLOBALS['_SERVER']['HTTPS'] = 'off';
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/current/url';
        $router = $this->getRouter();
        self::assertEquals('http://foo.bar/current/url', $router->getCurrentFull());
    }

    public function testGetCurrentFullSlash()
    {
        $GLOBALS['_SERVER']['SERVER_NAME'] = 'foo.bar';
        $GLOBALS['_SERVER']['SERVER_PORT'] = 80;
        $GLOBALS['_SERVER']['HTTPS'] = 'off';
        $GLOBALS['_SERVER']['REQUEST_URI'] = '/current/url/';
        $router = $this->getRouter();
        self::assertEquals('http://foo.bar/current/url/', $router->getCurrentFull());
    }
}
