<?php

declare(strict_types=1);

namespace Zaphyr\RouterTests;

use FastRoute\RouteCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Zaphyr\Router\Contracts\Attributes\GroupInterface;
use Zaphyr\Router\Contracts\Attributes\RouteInterface;
use Zaphyr\Router\Dispatcher;
use Zaphyr\Router\Exceptions\MethodNotAllowedException;
use Zaphyr\Router\Exceptions\NotFoundException;

class DispatcherTest extends TestCase
{
    protected Dispatcher $dispatcher;

    /**
     * @var RouteCollector&MockObject
     */
    protected RouteCollector|MockObject $routeCollectorMock;

    /**
     * @var GroupInterface&MockObject
     */
    protected GroupInterface|MockObject $groupMock;

    /**
     * @var RouteInterface&MockObject
     */
    protected RouteInterface|MockObject $routeMock;

    /**
     * @var UriInterface&MockObject
     */
    protected UriInterface|MockObject $uriMock;

    /**
     * @var ServerRequestInterface&MockObject
     */
    protected ServerRequestInterface|MockObject $requestMock;

    public function setUp(): void
    {
        $this->routeCollectorMock = $this->createMock(RouteCollector::class);
        $this->dispatcher = new Dispatcher($this->routeCollectorMock);
        $this->groupMock = $this->createMock(GroupInterface::class);
        $this->routeMock = $this->createMock(RouteInterface::class);
        $this->uriMock = $this->createMock(UriInterface::class);
        $this->requestMock = $this->createMock(ServerRequestInterface::class);
    }

    public function tearDown(): void
    {
        unset(
            $this->routeCollectorMock,
            $this->dispatcher,
            $this->groupMock,
            $this->routeMock,
            $this->uriMock,
            $this->requestMock
        );
    }

    /* -------------------------------------------------
     * ADD ROUTE
     * -------------------------------------------------
     */

    public function testAddRoute(): void
    {
        $this->routeMock->expects(self::once())->method('getMethods')->willReturn(['GET']);
        $this->routeMock->expects(self::once())->method('getPath')->willReturn('/');
        $this->routeCollectorMock
            ->expects(self::once())
            ->method('addRoute')
            ->with(['GET'], '/', $this->routeMock);
        $this->dispatcher->addRoute($this->routeMock);
    }

    /* -------------------------------------------------
     * HANDLE
     * -------------------------------------------------
     */

    public function testHandleStaticRoute(): void
    {
        $staticRoute['GET']['/'] = $this->routeMock;

        $this->routeCollectorMock->expects(self::once())->method('getData')->willReturn([$staticRoute, []]);
        $this->uriMock->expects(self::once())->method('getPath')->willReturn('/');
        $this->requestMock->expects(self::once())->method('getMethod')->willReturn('GET');
        $this->requestMock->expects(self::once())->method('getUri')->willReturn($this->uriMock);

        $this->dispatcher->handle($this->requestMock);
    }

    public function testHandleVariableRoute(): void
    {
        $this->routeMock->expects(self::once())->method('setParams')->with(['foo' => 'foo']);

        $variableRoute['GET'] = [
            [
                'regex' => '~^(?|/([^/]+))$~',
                'routeMap' => [
                    2 => [
                        $this->routeMock,
                        ['foo' => 'foo'],
                    ],
                ],
            ],
        ];

        $this->routeCollectorMock->expects(self::once())->method('getData')->willReturn([[], $variableRoute]);
        $this->uriMock->expects(self::once())->method('getPath')->willReturn('/foo');
        $this->requestMock->expects(self::once())->method('getMethod')->willReturn('GET');
        $this->requestMock->expects(self::once())->method('getUri')->willReturn($this->uriMock);

        $this->dispatcher->handle($this->requestMock);
    }

    public function testHandleNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $this->routeCollectorMock->expects(self::once())->method('getData')->willReturn([[], []]);
        $this->uriMock->expects(self::once())->method('getPath')->willReturn('/');
        $this->requestMock->expects(self::once())->method('getMethod')->willReturn('GET');
        $this->requestMock->expects(self::once())->method('getUri')->willReturn($this->uriMock);

        $this->dispatcher->handle($this->requestMock);
    }

    public function testHandleMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedException::class);

        $staticRoute['GET']['/'] = $this->routeMock;

        $this->routeCollectorMock->expects(self::once())->method('getData')->willReturn([$staticRoute, []]);
        $this->uriMock->expects(self::once())->method('getPath')->willReturn('/');
        $this->requestMock->expects(self::once())->method('getMethod')->willReturn('POST');
        $this->requestMock->expects(self::once())->method('getUri')->willReturn($this->uriMock);

        $this->dispatcher->handle($this->requestMock);
    }
}
