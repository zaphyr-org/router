<?php

declare(strict_types=1);

namespace Zaphyr\RouterTests\Attributes;

use PHPUnit\Framework\TestCase;
use Zaphyr\HttpMessage\Response;
use Zaphyr\Router\Attributes\Group;
use Zaphyr\Router\Contracts\Attributes\RouteInterface;
use Zaphyr\Router\Contracts\RouterInterface;
use Zaphyr\Router\Exceptions\MiddlewareException;
use Zaphyr\Router\Exceptions\RouteException;
use Zaphyr\RouterTests\TestAssets\Middleware;
use Zaphyr\RouterTests\TestAssets\MiddlewareTwo;

class GroupTest extends TestCase
{
    /*
     * @var Group
     */
    protected Group $group;

    protected function setUp(): void
    {
        $this->group = new Group('/group');
    }

    protected function tearDown(): void
    {
        unset($this->group);
    }

    /* -------------------------------------------------
     * INVOKE
     * -------------------------------------------------
     */

    public function testInvokeReturnsGroup(): void
    {
        $callback = static function () {
            $response = new Response();
            $response->getBody()->write('Hello World!');

            return $response;
        };

        $this->group->setCallable(function (Group $group) use ($callback): Group {
            $group->add('/route', ['GET'], $callback);

            return $group;
        });

        $route = $this->createMock(RouteInterface::class);
        $route->expects(self::once())
            ->method('setGroup')
            ->with($this->group);

        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('add')
            ->with('/group/route', ['GET'], $callback)
            ->willReturn($route);

        $this->group->setRouter($router);

        $this->group->__invoke();
    }

    /* -------------------------------------------------
     * SCHEME
     * -------------------------------------------------
     */

    public function testSetAndGetScheme(): void
    {
        foreach (['http', 'https'] as $scheme) {
            $this->group->setScheme($scheme);

            self::assertEquals($scheme, $this->group->getScheme());
        }
    }

    public function testSetSchemeRemovesColon(): void
    {
        $this->group->setScheme('https://');

        self::assertEquals('https', $this->group->getScheme());
    }

    public function testGroupSchemeIsPassedToRoute(): void
    {
        $this->group->setScheme('https');

        $route = $this->createMock(RouteInterface::class);
        $route->expects(self::once())
            ->method('setScheme')
            ->with('https');

        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('add')
            ->with('/group/route', ['GET'], static function () {
            })
            ->willReturn($route);

        $this->group->setRouter($router);

        $this->group->add('/route', ['GET'], static function () {
        });
    }

    public function testSetSchemeThrowsExceptionOnInvalidScheme(): void
    {
        $this->expectException(RouteException::class);

        $this->group->setScheme('ftp');
    }

    /* -------------------------------------------------
     * HOST
     * -------------------------------------------------
     */

    public function testSetAndGetHost(): void
    {
        $host = 'example.com';
        $this->group->setHost($host);

        self::assertEquals($host, $this->group->getHost());
    }

    public function testGroupHostIsPassedToRoute(): void
    {
        $this->group->setHost('example.com');

        $route = $this->createMock(RouteInterface::class);
        $route->expects(self::once())
            ->method('setHost')
            ->with('example.com');

        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('add')
            ->with('/group/route', ['GET'], static function () {
            })
            ->willReturn($route);

        $this->group->setRouter($router);

        $this->group->add('/route', ['GET'], static function () {
        });
    }

    /* -------------------------------------------------
     * PORT
     * -------------------------------------------------
     */

    public function testSetAndGetPort(): void
    {
        $port = 8080;
        $this->group->setPort($port);

        self::assertEquals($port, $this->group->getPort());
    }

    public function testGetPortReturnsNullWhenNoPortIsSet(): void
    {
        self::assertNull($this->group->getPort());
    }

    public function testGetPortReturnsNullWhenStandardPortIsUsed(): void
    {
        $this->group->setScheme('https')->setPort(443);

        self::assertNull($this->group->getPort());
    }

    public function testGroupPortIsPassedToRoute(): void
    {
        $this->group->setPort(8080);

        $route = $this->createMock(RouteInterface::class);
        $route->expects(self::once())
            ->method('setPort')
            ->with(8080);

        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('add')
            ->with('/group/route', ['GET'], static function () {
            })
            ->willReturn($route);

        $this->group->setRouter($router);

        $this->group->add('/route', ['GET'], static function () {
        });
    }

    public function testSetPortThrowsExceptionWhenPortIntIsTooLow(): void
    {
        $this->expectException(RouteException::class);

        $this->group->setPort(0);
    }

    public function testSetPortThrowsExceptionWhenPortIntIsTooHigh(): void
    {
        $this->expectException(RouteException::class);

        $this->group->setPort(65536);
    }

    /* -------------------------------------------------
     * MIDDLEWARE
     * -------------------------------------------------
     */

    public function testSetMiddlewareClassString(): void
    {
        $this->group->setMiddleware(MiddlewareTwo::class);

        self::assertEquals([MiddlewareTwo::class], $this->group->getMiddlewareStack());
    }

    public function testSetMiddlewareArrayClassString(): void
    {
        $this->group->setMiddleware([MiddlewareTwo::class]);

        self::assertEquals([MiddlewareTwo::class], $this->group->getMiddlewareStack());
    }

    public function testSetMiddlewareInstance(): void
    {
        $this->group->setMiddleware(new MiddlewareTwo());

        self::assertInstanceOf(MiddlewareTwo::class, $this->group->getMiddlewareStack()[0]);
    }

    public function testSetMiddlewareArrayInstances(): void
    {
        $this->group->setMiddleware([new MiddlewareTwo()]);

        self::assertInstanceOf(MiddlewareTwo::class, $this->group->getMiddlewareStack()[0]);
    }

    public function testShiftMiddlewareClassString(): void
    {
        $this->group->setMiddleware([MiddlewareTwo::class, MiddlewareTwo::class]);
        $this->group->shiftMiddleware();

        self::assertEquals([MiddlewareTwo::class], $this->group->getMiddlewareStack());
    }

    public function testShiftMiddlewareInstances(): void
    {
        $this->group->setMiddleware([new Middleware(1), new Middleware(2), new Middleware(3)]);
        $this->group->shiftMiddleware();

        self::assertEquals([new Middleware(2), new Middleware(3)], $this->group->getMiddlewareStack());
    }

    public function testShiftMiddlewareInstancesThrowsExceptionWhenNoMiddlewareAvailable(): void
    {
        $this->expectException(MiddlewareException::class);

        $this->group->shiftMiddleware();
    }

    public function testResolveMiddlewareClassString(): void
    {
        self::assertInstanceOf(
            MiddlewareTwo::class,
            $this->group->resolveMiddleware(MiddlewareTwo::class)
        );
    }

    public function testResolveMiddlewareInstance(): void
    {
        self::assertInstanceOf(
            MiddlewareTwo::class,
            $this->group->resolveMiddleware(new MiddlewareTwo())
        );
    }

    public function testResolveMiddlewareThrowsExceptionWhenClassDoesNotExist(): void
    {
        $this->expectException(MiddlewareException::class);

        $this->group->resolveMiddleware('NonExistingMiddleware');
    }
}
