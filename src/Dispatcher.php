<?php

declare(strict_types=1);

namespace Zaphyr\Route;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\Dispatcher\RegexBasedAbstract;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\Route\Contracts\Attributes\RouteInterface;
use Zaphyr\Route\Contracts\DispatcherInterface;
use Zaphyr\Route\Exceptions\MethodNotAllowedException;
use Zaphyr\Route\Exceptions\MiddlewareException;
use Zaphyr\Route\Exceptions\NotFoundException;
use Zaphyr\Route\Traits\MiddlewareAwareTrait;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Dispatcher extends RegexBasedAbstract implements DispatcherInterface
{
    use MiddlewareAwareTrait;

    /**
     * @param RouteCollector $routeCollector
     */
    public function __construct(
        protected RouteCollector $routeCollector = new RouteCollector(new Std(), new GroupCountBased())
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function addRoute(RouteInterface $route): static
    {
        $this->routeCollector->addRoute($route->getMethods(), $route->getPath(), $route);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws MiddlewareException if the middleware is not callable
     * @throws NotFoundException if no route was found for the given path
     * @throws MethodNotAllowedException if the HTTP method is not allowed for the requested route
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        [$this->staticRouteMap, $this->variableRouteData] = $this->routeCollector->getData();

        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $routeInfo = $this->dispatch($method, $path);

        if ($routeInfo[0] === self::NOT_FOUND) {
            throw new NotFoundException('Could not find route for path "' . $path . '"', 404);
        }

        if ($routeInfo[0] === self::METHOD_NOT_ALLOWED) {
            throw new MethodNotAllowedException(
                'Method "' . $method . '" not allowed. Allowed methods are: ' . implode(', ', $routeInfo[1]),
                405
            );
        }

        if ($routeInfo[0] === self::FOUND) {
            $this->setFoundMiddleware($routeInfo[1]->setParams($routeInfo[2]));
        }

        return $this->shiftMiddleware()->process($request, $this);
    }

    /**
     * @param RouteInterface $route
     *
     * @throws MiddlewareException if the middleware is not callable
     * @return void
     */
    protected function setFoundMiddleware(RouteInterface $route): void
    {
        foreach ($this->getMiddlewareStack() as $key => $middleware) {
            $this->middlewares[$key] = $this->resolveMiddleware($middleware);
        }

        if ($group = $route->getGroup()) {
            foreach ($group->getMiddlewareStack() as $middleware) {
                $this->addMiddleware($this->resolveMiddleware($middleware));
            }
        }

        foreach ($route->getMiddlewareStack() as $middleware) {
            $this->addMiddleware($this->resolveMiddleware($middleware));
        }

        $this->addMiddleware($route);
    }

    /**
     * {@inheritdoc}
     */
    protected function dispatchVariableRoute($routeData, $uri): array
    {
        foreach ($routeData as $data) {
            if (!preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            [$handler, $varNames] = $data['routeMap'][count($matches)];

            $vars = [];
            $i = 0;

            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }

            return [self::FOUND, $handler, $vars];
        }

        return [self::NOT_FOUND];
    }
}
