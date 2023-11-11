<?php

declare(strict_types=1);

namespace Zaphyr\RouterTests\Unit\Attributes;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zaphyr\HttpMessage\Response;
use Zaphyr\Router\Attributes\Group;
use Zaphyr\Router\Attributes\Route;
use Zaphyr\Router\Exceptions\MiddlewareException;
use Zaphyr\Router\Exceptions\RouteException;
use Zaphyr\Router\Traits\ContainerAwareTrait;
use Zaphyr\RouterTests\TestAssets\Controller;
use Zaphyr\RouterTests\TestAssets\DependencyInjectionController;
use Zaphyr\RouterTests\TestAssets\Middleware;
use Zaphyr\RouterTests\TestAssets\MiddlewareTwo;

class RouteTest extends TestCase
{
    /**
     * @var Route
     */
    protected Route $route;

    protected function setUp(): void
    {
        $this->route = new Route('/');
    }

    protected function tearDown(): void
    {
        unset($this->route);
    }

    /* -------------------------------------------------
     * CONSTRUCTOR
     * -------------------------------------------------
     */

    public function testConstructorAndGetterMethods(): void
    {
        $route = new Route(
            path: '/',
            methods: ['GET', 'POST'],
            name: 'home',
            middlewares: [Middleware::class],
            scheme: 'https',
            host: 'example.com',
            port: 443,
        );

        self::assertEquals('/', $route->getPath());
        self::assertEquals(['GET', 'POST'], $route->getMethods());
        self::assertEquals('home', $route->getName());
        self::assertEquals([Middleware::class], $route->getMiddlewareStack());
        self::assertEquals('https', $route->getScheme());
        self::assertEquals('example.com', $route->getHost());
        self::assertNull($route->getPort());
    }

    /* -------------------------------------------------
     * PATH
     * -------------------------------------------------
     */

    public function testSetAndGetPath(): void
    {
        $path = '/home';
        $this->route->setPath($path);

        self::assertEquals($path, $this->route->getPath());
    }

    /* -------------------------------------------------
     * METHODS
     * -------------------------------------------------
     */

    public function testSetAndGetMethods(): void
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
        $this->route->setMethods($methods);

        self::assertEquals($methods, $this->route->getMethods());
    }

    public function testSetMethodUppercase(): void
    {
        $this->route->setMethods(['get']);

        self::assertEquals(['GET'], $this->route->getMethods());
    }

    public function testSetMethodThrowsExceptionOnInvalidMethod(): void
    {
        $this->expectException(RouteException::class);

        $this->route->setMethods(['TRACE']);
    }

    /* -------------------------------------------------
     * CALLABLE
     * -------------------------------------------------
     */

    public function testSetCallableWithClosure(): void
    {
        $this->route->setCallable(static function () {
            $response = new Response();
            $response->getBody()->write('callable');

            return $response;
        });

        self::assertEquals('callable', (string)$this->route->getCallable()->__invoke()->getBody());
    }

    public function testSetCallableWithInvokableContainer(): void
    {
        $this->route->setCallable(new Controller());

        self::assertEquals('callable', (string)$this->route->getCallable()->__invoke()->getBody());
    }

    public function testSetCallableWithControllerInvokableClassString(): void
    {
        $this->route->setCallable(Controller::class);

        self::assertEquals('callable', (string)$this->route->getCallable()->__invoke()->getBody());
    }

    public function testSetCallableWithControllerAndMethodArray(): void
    {
        $controller = new Controller();

        $this->route->setCallable([$controller, 'index']);

        self::assertEquals([$controller, 'index'], $this->route->getCallable());
    }

    public function testSetCallableWithControllerObjectAndMethodString(): void
    {
        $this->route->setCallable([new Controller(), 'index']);

        self::assertEquals([new Controller(), 'index'], $this->route->getCallable());
    }

    public function testSetCallableWithControllerAndMethodString(): void
    {
        $this->route->setCallable(Controller::class . '@index');

        self::assertEquals([new Controller(), 'index'], $this->route->getCallable());
    }

    public function testGetCallableThrowsExceptionWhenNoCallableResolved(): void
    {
        $this->expectException(RouteException::class);

        $this->route->getCallable();
    }

    public function testGetCallableInvokableThrowsExceptionWhenDependencyIsNotResolvable(): void
    {
        $this->expectException(RouteException::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willThrowException(new \Exception());

        $this->route->setCallable(DependencyInjectionController::class);

        $this->route->setContainer($container);
        $this->route->getCallable();
    }

    public function testGetCallableArrayControllerThrowsExceptionWhenDependencyIsNotResolvable(): void
    {
        $this->expectException(RouteException::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willThrowException(new \Exception());

        $this->route->setCallable([DependencyInjectionController::class, 'index']);

        $this->route->setContainer($container);
        $this->route->getCallable();
    }

    /* -------------------------------------------------
     * NAME
     * -------------------------------------------------
     */

    public function testSetAndGetName(): void
    {
        $name = 'home';
        $this->route->setName($name);

        self::assertEquals($name, $this->route->getName());
    }

    /* -------------------------------------------------
     * GROUP
     * -------------------------------------------------
     */

    public function testSetAndGetGroup(): void
    {
        $group = new Group('/group');
        $this->route->setGroup($group);

        self::assertEquals($group, $this->route->getGroup());
    }

    /* -------------------------------------------------
     * PARAMS
     * -------------------------------------------------
     */

    public function testSetAndGetParams(): void
    {
        $params = ['id' => '1'];
        $this->route->setParams($params);

        self::assertEquals($params, $this->route->getParams());
    }

    /* -------------------------------------------------
     * SCHEME
     * -------------------------------------------------
     */

    public function testSetAndGetScheme(): void
    {
        foreach (['http', 'https'] as $scheme) {
            $this->route->setScheme($scheme);

            self::assertEquals($scheme, $this->route->getScheme());
        }
    }

    public function testSetSchemeRemovesColon(): void
    {
        $this->route->setScheme('https://');

        self::assertEquals('https', $this->route->getScheme());
    }

    public function testSetSchemeThrowsExceptionOnInvalidScheme(): void
    {
        $this->expectException(RouteException::class);

        $this->route->setScheme('ftp');
    }

    /* -------------------------------------------------
     * HOST
     * -------------------------------------------------
     */

    public function testSetAndGetHost(): void
    {
        $host = 'example.com';
        $this->route->setHost($host);

        self::assertEquals($host, $this->route->getHost());
    }

    /* -------------------------------------------------
     * PORT
     * -------------------------------------------------
     */

    public function testSetAndGetPort(): void
    {
        $port = 8080;
        $this->route->setPort($port);

        self::assertEquals($port, $this->route->getPort());
    }

    public function testGetPortReturnsNullWhenNoPortIsSet(): void
    {
        self::assertNull($this->route->getPort());
    }

    public function testGetPortReturnsNullWhenStandardPortIsUsed(): void
    {
        $this->route->setScheme('https')->setPort(443);

        self::assertNull($this->route->getPort());
    }

    public function testSetPortThrowsExceptionWhenPortIntIsTooLow(): void
    {
        $this->expectException(RouteException::class);

        $this->route->setPort(0);
    }

    public function testSetPortThrowsExceptionWhenPortIntIsTooHigh(): void
    {
        $this->expectException(RouteException::class);

        $this->route->setPort(65536);
    }

    /* -------------------------------------------------
     * MIDDLEWARE
     * -------------------------------------------------
     */

    public function testSetMiddlewareClassString(): void
    {
        $this->route->setMiddleware(MiddlewareTwo::class);

        self::assertEquals([MiddlewareTwo::class], $this->route->getMiddlewareStack());
    }

    public function testSetMiddlewareArrayClassString(): void
    {
        $this->route->setMiddleware([MiddlewareTwo::class]);

        self::assertEquals([MiddlewareTwo::class], $this->route->getMiddlewareStack());
    }

    public function testSetMiddlewareInstance(): void
    {
        $this->route->setMiddleware(new MiddlewareTwo());

        self::assertInstanceOf(MiddlewareTwo::class, $this->route->getMiddlewareStack()[0]);
    }

    public function testSetMiddlewareArrayInstances(): void
    {
        $this->route->setMiddleware([new MiddlewareTwo()]);

        self::assertInstanceOf(MiddlewareTwo::class, $this->route->getMiddlewareStack()[0]);
    }

    public function testShiftMiddlewareClassString(): void
    {
        $this->route->setMiddleware([MiddlewareTwo::class, MiddlewareTwo::class]);
        $this->route->shiftMiddleware();

        self::assertEquals([MiddlewareTwo::class], $this->route->getMiddlewareStack());
    }

    public function testShiftMiddlewareInstances(): void
    {
        $this->route->setMiddleware([new Middleware(1), new Middleware(2), new Middleware(3)]);
        $this->route->shiftMiddleware();

        self::assertEquals([new Middleware(2), new Middleware(3)], $this->route->getMiddlewareStack());
    }

    public function testShiftMiddlewareInstancesThrowsExceptionWhenNoMiddlewareAvailable(): void
    {
        $this->expectException(MiddlewareException::class);

        $this->route->shiftMiddleware();
    }

    public function testResolveMiddlewareClassString(): void
    {
        self::assertInstanceOf(
            MiddlewareTwo::class,
            $this->route->resolveMiddleware(MiddlewareTwo::class)
        );
    }

    public function testResolveMiddlewareInstance(): void
    {
        self::assertInstanceOf(
            MiddlewareTwo::class,
            $this->route->resolveMiddleware(new MiddlewareTwo())
        );
    }

    public function testResolveMiddlewareThrowsExceptionWhenClassDoesNotExist(): void
    {
        $this->expectException(MiddlewareException::class);

        $this->route->resolveMiddleware('NonExistingMiddleware');
    }

    public function testResolveThrowsExceptionWhenMiddlewareNotResolvable(): void
    {
        $this->expectException(MiddlewareException::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willThrowException(new \Exception());

        $this->route->setContainer($container);
        $this->route->resolveMiddleware(MiddlewareTwo::class);
    }

    /* -------------------------------------------------
     * CONTAINER
     * -------------------------------------------------
     */

    public function testSetAndGetContainerInstance(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $this->route->setContainer($container);

        self::assertInstanceOf(ContainerInterface::class, $this->route->getContainer());
    }

    public function testGetContainerReturnsNullWhenNoContainerInstanceAvailable(): void
    {
        self::assertNull($this->route->getContainer());
    }

    public function testSetContainerThrowsExceptionWhenInstanceDoesNotImplementContainerAwareInterface(): void
    {
        $this->expectException(RouteException::class);

        $route = new class {
            use ContainerAwareTrait;
        };

        $route->setContainer(
            new class implements ContainerInterface {
                use ContainerAwareTrait;

                public function get($id)
                {
                }

                public function has($id): bool
                {
                    return true;
                }
            }
        );
    }
}
