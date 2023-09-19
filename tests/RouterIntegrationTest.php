<?php

declare(strict_types=1);

namespace Zaphyr\RouterTests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\Container\Container;
use Zaphyr\HttpMessage\Response;
use Zaphyr\HttpMessage\ServerRequest;
use Zaphyr\Router\Attributes\Group;
use Zaphyr\Router\Exceptions\MethodNotAllowedException;
use Zaphyr\Router\Exceptions\NotFoundException;
use Zaphyr\Router\Exceptions\RouteException;
use Zaphyr\Router\Router;
use Zaphyr\RouterTests\TestAssets\ConditionController;
use Zaphyr\RouterTests\TestAssets\ConditionGroupController;
use Zaphyr\RouterTests\TestAssets\Controller;
use Zaphyr\RouterTests\TestAssets\DependencyInjectionController;
use Zaphyr\RouterTests\TestAssets\GroupController;
use Zaphyr\RouterTests\TestAssets\Middleware;
use Zaphyr\RouterTests\TestAssets\MiddlewareController;

class RouterIntegrationTest extends TestCase
{
    protected Router $router;

    public function setUp(): void
    {
        $this->router = new Router();
    }

    public function tearDown(): void
    {
        unset($this->router);
    }

    /* -------------------------------------------------
     * CONSTRUCTOR
     * -------------------------------------------------
     */

    public function testConstructorSetControllers(): void
    {
        $router = new Router(controllers: [Controller::class]);

        $response = $router->handle(new ServerRequest(uri: '/index'));

        self::assertSame('index', (string)$response->getBody());
    }

     public function testConstructorSetRoutePatterns(): void
    {
        $router = new Router(routePatterns: ['slug' => '[a-z0-9-]+']);

        $router->get('/{slug}', function ($request, $params) {
            $response = new Response();
            $response->getBody()->write(json_encode($params));

            return $response;
        });

        $response = $router->handle(new ServerRequest(uri: '/foo-1'));

        self::assertSame(json_encode(['slug' => 'foo-1']), (string)$response->getBody());
    }

    /* -------------------------------------------------
     * CONTROLLER ROUTES
     * -------------------------------------------------
     */

    public function testSetControllerRoutes(): void
    {
        $this->router->setControllerRoutes([Controller::class]);

        $response = $this->router->handle(new ServerRequest(uri: '/index'));

        self::assertSame('index', (string)$response->getBody());
    }

    /* -------------------------------------------------
     * ROUTE PATTERNS
     * -------------------------------------------------
     */

    /**
     * @param string $pattern
     * @param string $path
     * @param array  $expected
     *
     * @dataProvider predefinedRoutePatternsDataProvider
     */
    public function testWithPredefinedRoutePatterns(string $pattern, string $path, array $expected): void
    {
        $this->router->get($pattern, function ($request, $params) {
            $response = new Response();
            $response->getBody()->write(json_encode($params));

            return $response;
        });

        $response = $this->router->handle(new ServerRequest(uri: $path));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(json_encode($expected), (string)$response->getBody());
    }

    public function testSetRoutePatterns(): void
    {
        $this->router->setRoutePatterns([
            'slug' => '[a-z0-9-]+'
        ]);

        $this->router->get('/{slug}', function ($request, $params) {
            $response = new Response();
            $response->getBody()->write(json_encode($params));

            return $response;
        });

        $response = $this->router->handle(new ServerRequest(uri: '/foo-1'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(json_encode(['slug' => 'foo-1']), (string)$response->getBody());
    }

    public function testInvalidRoutePatternThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->get('/{id:numeric}', function ($request, $params) {
            $response = new Response();
            $response->getBody()->write(json_encode($params));

            return $response;
        });

        $this->router->handle(new ServerRequest(uri: '/alpha'));
    }

    /**
     * @return array<string, array<string, string|array>>
     */
    public static function predefinedRoutePatternsDataProvider(): array
    {
        return [
            'numeric' => [
                'pattern' => '/{id:numeric}',
                'path' => '/1',
                'expected' => ['id' => '1']
            ],
            'alpha' => [
                'pattern' => '/{name:alpha}',
                'path' => '/foo',
                'expected' => ['name' => 'foo']
            ],
            'alphanum' => [
                'pattern' => '/{name:alphanum}',
                'path' => '/foo1',
                'expected' => ['name' => 'foo1']
            ],
            'alphanum_dash' => [
                'pattern' => '/{slug:alphanum_dash}',
                'path' => '/foo-1',
                'expected' => ['slug' => 'foo-1']
            ],
        ];
    }

    /* -------------------------------------------------
     * ADD
     * -------------------------------------------------
     */

    public function testAdd(): void
    {
        $path = '/foo';
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
        $callable = static fn() => new Response();

        foreach ($methods as $method) {
            $route = $this->router->add($path, $methods, $callable);

            self::assertSame('/foo', $route->getPath());
            self::assertContains($method, $route->getMethods());
        }
    }

    public function testAddWithControllerInvokable(): void
    {
        $this->router->add(
            '/foo',
            ['GET'],
            new class {
                public function __invoke(ServerRequestInterface $request): ResponseInterface
                {
                    $response = new Response();
                    $response->getBody()->write('foo');

                    return $response;
                }
            }
        );

        $response = $this->router->handle(new ServerRequest(uri: '/foo'));

        self::assertSame('foo', (string)$response->getBody());
    }

    public function testAddWithControllerInvokableString(): void
    {
        $this->router->add('/foo', ['GET'], Controller::class);

        $response = $this->router->handle(new ServerRequest(uri: '/foo'));

        self::assertSame('callable', (string)$response->getBody());
    }

    public function testAddWithControllerAndMethodArray(): void
    {
        $this->router->add('/', ['GET'], [Controller::class, 'index']);

        $response = $this->router->handle(new ServerRequest(uri: '/'));

        self::assertSame('index', (string)$response->getBody());
    }

    public function testAddWithControllerObjectAndMethodString(): void
    {
        $this->router->add('/', ['GET'], [new Controller(), 'index']);
        $response = $this->router->handle(new ServerRequest(uri: '/'));

        self::assertSame('index', (string)$response->getBody());
    }

    public function testAddWithControllerAndMethodString(): void
    {
        $this->router->add('/', ['GET'], Controller::class . '@index');
        $response = $this->router->handle(new ServerRequest(uri: '/'));

        self::assertSame('index', (string)$response->getBody());
    }

    public function testAddThrowsExceptionOnInvalidHttpMethod(): void
    {
        $this->expectException(RouteException::class);

        $this->router->add('/', ['INVALID'], static fn() => new Response());
    }

    /* -------------------------------------------------
     * ANY
     * -------------------------------------------------
     */

    public function testAny(): void
    {
        $path = '/foo';
        $callable = static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        };

        $this->router->any($path, $callable);

        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'] as $method) {
            $response = $this->router->handle(new ServerRequest($method, '/foo'));

            self::assertSame('foo', (string)$response->getBody());
        }
    }

    public function testAnyWithAttribute(): void
    {
        $this->router->setControllerRoutes([Controller::class]);

        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'] as $method) {
            $response = $this->router->handle(new ServerRequest($method, '/any'));

            self::assertSame('any', (string)$response->getBody());
        }
    }

    /* -------------------------------------------------
     * GET
     * -------------------------------------------------
     */

    public function testGet(): void
    {
        $path = '/foo';
        $callable = static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        };

        $this->router->get($path, $callable);

        $response = $this->router->handle(new ServerRequest(uri: '/foo'));

        self::assertSame('foo', (string)$response->getBody());
    }

    public function testGetWithAttribute(): void
    {
        $this->router->setControllerRoutes([Controller::class]);

        $response = $this->router->handle(new ServerRequest(uri: '/get'));

        self::assertSame('get', (string)$response->getBody());
    }

    /* -------------------------------------------------
     * POST
     * -------------------------------------------------
     */

    public function testPost(): void
    {
        $path = '/foo';
        $callable = static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        };

        $this->router->post($path, $callable);

        $response = $this->router->handle(new ServerRequest('POST', '/foo'));

        self::assertSame('foo', (string)$response->getBody());
    }

    public function testPostWithAttribute(): void
    {
        $this->router->setControllerRoutes([Controller::class]);

        $response = $this->router->handle(new ServerRequest('POST', '/post'));

        self::assertSame('post', (string)$response->getBody());
    }

    /* -------------------------------------------------
     * PUT
     * -------------------------------------------------
     */

    public function testPut(): void
    {
        $path = '/foo';
        $callable = static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        };

        $this->router->put($path, $callable);

        $response = $this->router->handle(new ServerRequest('PUT', '/foo'));

        self::assertSame('foo', (string)$response->getBody());
    }

    public function testPutWithAttribute(): void
    {
        $this->router->setControllerRoutes([Controller::class]);

        $response = $this->router->handle(new ServerRequest('PUT', '/put'));

        self::assertSame('put', (string)$response->getBody());
    }

    /* -------------------------------------------------
     * PATCH
     * -------------------------------------------------
     */

    public function testPatch(): void
    {
        $path = '/foo';
        $callable = static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        };

        $this->router->patch($path, $callable);

        $response = $this->router->handle(new ServerRequest('PATCH', '/foo'));

        self::assertSame('foo', (string)$response->getBody());
    }

    public function testPatchWithAttribute(): void
    {
        $this->router->setControllerRoutes([Controller::class]);

        $response = $this->router->handle(new ServerRequest('PATCH', '/patch'));

        self::assertSame('patch', (string)$response->getBody());
    }

    /* -------------------------------------------------
     *  DELETE
     * -------------------------------------------------
     */

    public function testDelete(): void
    {
        $path = '/foo';
        $callable = static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        };

        $this->router->delete($path, $callable);

        $response = $this->router->handle(new ServerRequest('DELETE', '/foo'));

        self::assertSame('foo', (string)$response->getBody());
    }

    public function testDeleteWithAttribute(): void
    {
        $this->router->setControllerRoutes([Controller::class]);

        $response = $this->router->handle(new ServerRequest('DELETE', '/delete'));

        self::assertSame('delete', (string)$response->getBody());
    }

    /* -------------------------------------------------
     * HEAD
     * -------------------------------------------------
     */

    public function testHead(): void
    {
        $path = '/foo';
        $callable = static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        };

        $this->router->head($path, $callable);

        $response = $this->router->handle(new ServerRequest('HEAD', '/foo'));

        self::assertSame('foo', (string)$response->getBody());
    }

    public function testHeadWithAttribute(): void
    {
        $this->router->setControllerRoutes([Controller::class]);

        $response = $this->router->handle(new ServerRequest('HEAD', '/head'));

        self::assertSame('head', (string)$response->getBody());
    }

    /* -------------------------------------------------
     * OPTIONS
     * -------------------------------------------------
     */

    public function testOptions(): void
    {
        $path = '/foo';
        $callable = static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        };

        $this->router->options($path, $callable);

        $response = $this->router->handle(new ServerRequest('OPTIONS', '/foo'));

        self::assertSame('foo', (string)$response->getBody());
    }

    public function testOptionsWithAttribute(): void
    {
        $this->router->setControllerRoutes([Controller::class]);

        $response = $this->router->handle(new ServerRequest('OPTIONS', '/options'));

        self::assertSame('options', (string)$response->getBody());
    }

    /* -------------------------------------------------
     * GROUP
     * -------------------------------------------------
     */

    public function testGroup(): void
    {
        $this->router->group('/foo', static function (Group $group) {
            $group->add('/bar', ['GET'], static function () {
                $response = new Response();
                $response->getBody()->write('bar');

                return $response;
            });
        });

        $response = $this->router->handle(new ServerRequest(uri: '/foo/bar'));

        self::assertSame('bar', (string)$response->getBody());
    }

    public function testGroupWithAttributes(): void
    {
        $this->router->setControllerRoutes([GroupController::class]);

        $response = $this->router->handle(new ServerRequest(uri: '/group/foo'));

        self::assertSame('group.foo', (string)$response->getBody());
    }

    /* -------------------------------------------------
     * HANDLE
     * -------------------------------------------------
     */

    public function testHandleThrowsExceptionWhenHandlerIsNotCallable(): void
    {
        $this->expectException(RouteException::class);

        $this->router->add('/foo', ['GET'], 'invalidHandler');
        $this->router->handle(new ServerRequest(uri: '/foo'));
    }

    public function testHandleThrowsNotFoundExceptionWhenNoRouteFound(): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->get('bar', static fn() => new Response());

        try {
            $this->router->handle(new ServerRequest(uri: '/foo'));
        } catch (NotFoundException $e) {
            self::assertSame('Could not find route for path "/foo"', $e->getMessage());
            self::assertSame(404, $e->getCode());
            throw $e;
        }
    }

    public function testHandleThrowsMethodNotAllowedExceptionWhenMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedException::class);

        $this->router->get('foo', static fn() => new Response());
        $this->router->handle(new ServerRequest(uri: '/foo', method: 'POST'));
    }

    public function testHandleWildcardParamsRoute(): void
    {
        $this->router->get('/foo/{bar}', function ($request, $params) {
            $response = new Response();
            $response->getBody()->write(json_encode($params));

            return $response;
        });

        $response = $this->router->handle(new ServerRequest(uri: '/foo/bar'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{"bar":"bar"}', (string)$response->getBody());
    }

    public function testHandleWildcardParamsRouteThrowsNotFoundOnInvalidRoute(): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->get('/foo/{bar}', function ($request, $params) {
            $response = new Response();
            $response->getBody()->write(json_encode($params));

            return $response;
        });

        $this->router->handle(new ServerRequest(uri: '/foo'));
    }

    public function testHandleWithAttributesWildcardParamsRoute(): void
    {
        $this->router->setControllerRoutes([Controller::class]);

        $response = $this->router->handle(new ServerRequest(uri: '/wildcard-route/1'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{"id":"1"}', (string)$response->getBody());
    }

    public function testHandleWithAttributesWildcardParamsRouteThrowsExceptionOnInvalidRoute(): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->setControllerRoutes([Controller::class]);

        $this->router->handle(new ServerRequest(uri: '/wildcard-route'));
    }

    /* -------------------------------------------------
     * GET NAMED ROUTE
     * -------------------------------------------------
     */

    public function testGetNamedRoute(): void
    {
        $this->router->add('/foo', ['GET'], static fn() => new Response())->setName('route.foo');

        self::assertSame('/foo', $this->router->getNamedRoute('route.foo')->getPath());
    }

    public function testGetNamedRouteWithAttribute(): void
    {
        $this->router->setControllerRoutes([Controller::class]);

        self::assertSame('/name', $this->router->getNamedRoute('controller.name')->getPath());
    }

    public function testGetNamedRouteThrowsExceptionWhenRouteNotFound(): void
    {
        $this->expectException(RouteException::class);

        $this->router->getNamedRoute('route.invalid');
    }

    /* -------------------------------------------------
     * MIDDLEWARES
     * -------------------------------------------------
     */

    public function testMiddlewareIsOrderedCorrectly(): void
    {
        $middlewareOne = new Middleware(1);
        $middlewareTwo = new Middleware(2);
        $middlewareThree = new Middleware(3);

        $this->router->setMiddleware($middlewareOne);
        $this->router->group(
            '/foo',
            static function (Group $group) use ($middlewareThree) {
                $group->get(
                    '/bar',
                    static fn() => new Response(),
                )->setMiddleware($middlewareThree);
            },
        )->setMiddlewares([$middlewareTwo]);

        $this->router->handle(new ServerRequest(uri: '/foo/bar'));

        self::assertSame(1, $middlewareOne->getCounter());
        self::assertSame(2, $middlewareTwo->getCounter());
        self::assertSame(3, $middlewareThree->getCounter());
    }

    public function testMiddlewareWithAttributeIsOrderedCorrectly(): void
    {
        $this->router->setMiddleware(new Middleware(1));
        $this->router->setControllerRoutes([MiddlewareController::class]);
        $this->router->handle(new ServerRequest(uri: '/middleware/index'));

        self::assertSame(1, $this->router->getMiddlewareStack()[0]->getCounter());
        self::assertSame(
            3,
            $this->router->getNamedRoute('middleware.index')->getMiddlewareStack()[0]->getCounter()
        );
    }

    /* -------------------------------------------------
     * ROUTE CONDITIONS (SCHEME | HOST | PORT)
     * -------------------------------------------------
     */

    public function testHandleWithRouteScheme(): void
    {
        $this->router->add('/foo', ['GET'], static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        })->setScheme('https');

        $request = new ServerRequest(uri: '/foo');
        $request = $request->withUri($request->getUri()->withScheme('https'));

        $response = $this->router->handle($request);

        self::assertSame('foo', (string)$response->getBody());
    }

    public function testHandleWithRouteSchemeThrowsNotFoundExceptionWhenSchemesNotMatch(): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->add('/foo', ['GET'], static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        })->setScheme('https');

        $request = new ServerRequest(uri: '/foo');
        $request = $request->withUri($request->getUri()->withScheme('http'));

        $this->router->handle($request);
    }

    public function testHandleWithGroupScheme(): void
    {
        $this->router->group('/foo', static function (Group $group) {
            $group->add('/bar', ['GET'], static function () {
                $response = new Response();
                $response->getBody()->write('bar');

                return $response;
            });
        })->setScheme('https');

        $request = new ServerRequest(uri: '/foo/bar');
        $request = $request->withUri($request->getUri()->withScheme('https'));

        $response = $this->router->handle($request);

        self::assertSame('bar', (string)$response->getBody());
    }

    public function testHandleWithGroupSchemeThrowsNotFoundExceptionWhenSchemesNotMatch(): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->group('/foo', static function (Group $group) {
            $group->add('/bar', ['GET'], static function () {
                $response = new Response();
                $response->getBody()->write('bar');

                return $response;
            });
        })->setScheme('https');

        $request = new ServerRequest(uri: '/foo/bar');
        $request = $request->withUri($request->getUri()->withScheme('http'));

        $this->router->handle($request);
    }

    public function testHandleWithRouteHost(): void
    {
        $this->router->add('/foo', ['GET'], static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        })->setHost('example.com');

        $request = new ServerRequest(uri: '/foo');
        $request = $request->withUri($request->getUri()->withHost('example.com'));

        $response = $this->router->handle($request);

        self::assertSame('foo', (string)$response->getBody());
    }

    public function testHandleWithRouteHostThrowsNotFoundExceptionWhenHostsNotMatch(): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->add('/foo', ['GET'], static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        })->setHost('example.com');

        $request = new ServerRequest(uri: '/foo');
        $request = $request->withUri($request->getUri()->withHost('example.org'));

        $this->router->handle($request);
    }

    public function testHandleWithGroupHost(): void
    {
        $this->router->group('/foo', static function (Group $group) {
            $group->add('/bar', ['GET'], static function () {
                $response = new Response();
                $response->getBody()->write('bar');

                return $response;
            });
        })->setHost('example.com');

        $request = new ServerRequest(uri: '/foo/bar');
        $request = $request->withUri($request->getUri()->withHost('example.com'));

        $response = $this->router->handle($request);

        self::assertSame('bar', (string)$response->getBody());
    }

    public function testHandleWithGroupHostThrowsNotFoundExceptionWhenHostsNotMatch(): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->group('/foo', static function (Group $group) {
            $group->add('/bar', ['GET'], static function () {
                $response = new Response();
                $response->getBody()->write('bar');

                return $response;
            });
        })->setHost('example.com');

        $request = new ServerRequest(uri: '/foo/bar');
        $request = $request->withUri($request->getUri()->withHost('example.org'));

        $this->router->handle($request);
    }

    public function testHandleWithRoutePort(): void
    {
        $this->router->add('/foo', ['GET'], static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        })->setPort(8080);

        $request = new ServerRequest(uri: '/foo');
        $request = $request->withUri($request->getUri()->withPort(8080));

        $response = $this->router->handle($request);

        self::assertSame('foo', (string)$response->getBody());
    }

    public function testHandleWithRoutePortThrowsNotFoundExceptionWhenPortsNotMatch(): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->add('/foo', ['GET'], static function () {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        })->setPort(8080);

        $request = new ServerRequest(uri: '/foo');
        $request = $request->withUri($request->getUri()->withPort(80));

        $this->router->handle($request);
    }

    public function testHandleWithGroupPort(): void
    {
        $this->router->group('/foo', static function (Group $group) {
            $group->add('/bar', ['GET'], static function () {
                $response = new Response();
                $response->getBody()->write('bar');

                return $response;
            });
        })->setPort(8080);

        $request = new ServerRequest(uri: '/foo/bar');
        $request = $request->withUri($request->getUri()->withPort(8080));

        $response = $this->router->handle($request);

        self::assertSame('bar', (string)$response->getBody());
    }

    public function testHandleWithGroupPortThrowsNotFoundExceptionWhenPortsNotMatch(): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->group('/foo', static function (Group $group) {
            $group->add('/bar', ['GET'], static function () {
                $response = new Response();
                $response->getBody()->write('bar');

                return $response;
            });
        })->setPort(8080);

        $request = new ServerRequest(uri: '/foo/bar');
        $request = $request->withUri($request->getUri()->withPort(80));

        $this->router->handle($request);
    }

    public function testHandleWithAttributeConditions(): void
    {
        $this->router->setControllerRoutes([ConditionController::class]);

        $request = new ServerRequest(uri: '/condition');
        $uri = $request->getUri()->withScheme('https')->withHost('example.com')->withPort(443);
        $request = $request->withUri($uri);

        $response = $this->router->handle($request);

        self::assertSame('condition', (string)$response->getBody());
    }

    public function testHandleWithAttributeConditionThrowsExceptionOnMismatch(): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->setControllerRoutes([ConditionController::class]);

        $response = $this->router->handle(new ServerRequest(uri: '/condition'));

        self::assertSame('condition', (string)$response->getBody());
    }

    public function testHandleWithAttributeGroupConditions(): void
    {
        $this->router->setControllerRoutes([ConditionGroupController::class]);

        $request = new ServerRequest(uri: '/condition-group/index');
        $uri = $request->getUri()->withScheme('https')->withHost('example.com')->withPort(8080);
        $request = $request->withUri($uri);

        $response = $this->router->handle($request);

        self::assertSame('condition-group.index', (string)$response->getBody());
    }

    public function testHandleWithAttributeGroupConditionThrowsExceptionOnMismatch(): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->setControllerRoutes([ConditionGroupController::class]);

        $response = $this->router->handle(new ServerRequest(uri: '/condition-group/index'));

        self::assertSame('condition-group.index', (string)$response->getBody());
    }

    public function testSetSchemeIsLowercase(): void
    {
        $route = $this->router->get('/foo', static fn() => new Response())->setScheme('HTTPS://');

        self::assertEquals('https', $route->getScheme());
    }

    public function testSetSchemeThrowsExceptionOnInvalidScheme(): void
    {
        $this->expectException(RouteException::class);

        $this->router->get('/foo', static fn() => new Response())->setScheme('foo');
    }

    public function testSetPortThrowsExceptionOnInvalidPort(): void
    {
        $this->expectException(RouteException::class);

        $this->router->get('/foo', static fn() => new Response())->setPort(65537);
    }

    /* -------------------------------------------------
     * GET PATH FROM NAME
     * -------------------------------------------------
     */

    public function testGetPathFromName(): void
    {
        $this->router->get('/foo/{id:numeric}', static fn () => new Response())->setName('foo');

        self::assertEquals('/foo/123', $this->router->getPathFromName('foo', ['id' => 123]));
    }

    public function testGetPathFromNameWithoutParams(): void
    {
        $this->router->get('/foo', static fn () => new Response())->setName('foo');

        self::assertEquals('/foo', $this->router->getPathFromName('foo'));
    }

    public function testGetPathFromNameThrowsExceptionOnMissingParams(): void
    {
        $this->expectException(RouteException::class);

        $this->router->get('/foo/{id:numeric}', static fn () => new Response())->setName('foo');

        self::assertEquals('/foo/123', $this->router->getPathFromName('foo'));
    }

    /* -------------------------------------------------
     * CONTAINER
     * -------------------------------------------------
     */

    public function testDependencyInjectionController(): void
    {
        $this->router->setContainer(new Container());

        $this->router->setControllerRoutes([DependencyInjectionController::class]);

        $response = $this->router->handle(new ServerRequest(uri: '/container'));

        self::assertSame('hello from foo', (string)$response->getBody());
    }

    public function testDependencyInjectionThrowsExceptionWhenDependencyIsNotResolvable(): void
    {
        $this->expectException(RouteException::class);

        $this->router->setControllerRoutes([DependencyInjectionController::class]);

        $response = $this->router->handle(new ServerRequest(uri: '/container'));

        self::assertSame('hello from foo', (string)$response->getBody());
    }
}
