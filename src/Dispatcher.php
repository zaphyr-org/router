<?php

declare(strict_types=1);

namespace Zaphyr\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zaphyr\Route\Attributes\Route;
use Zaphyr\Route\Contracts\MiddlewareAwareInterface;
use Zaphyr\Route\Exceptions\MethodNotAllowedException;
use Zaphyr\Route\Exceptions\MiddlewareException;
use Zaphyr\Route\Exceptions\NotFoundException;
use Zaphyr\Route\Traits\MiddlewareAwareTrait;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Dispatcher implements MiddlewareAwareInterface, RequestHandlerInterface
{
    use MiddlewareAwareTrait;

    /**
     * @param Route[] $routes
     */
    public function __construct(protected array $routes)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @throws MiddlewareException
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        $method = $request->getMethod();

        if (count($this->routes) === 0) {
            throw new NotFoundException('Could not find route for path "' . $path . '"', 404);
        }

        foreach ($this->routes as $route) {
            if ($route->getPath() === $path) {
                if (!in_array($method, $route->getMethods())) {
                    throw new MethodNotAllowedException(
                        'Method "' . $method . '" not allowed. Allowed methods are: ' .
                        implode(', ', $route->getMethods()),
                        405
                    );
                }

                if ($route->getScheme() !== $uri->getScheme()) {
                    break;
                }

                if ($route->getHost() !== $uri->getHost()) {
                    break;
                }

                if ($route->getPort() !== $uri->getPort()) {
                    break;
                }

                $this->setFoundMiddleware($route);

                return $this->shiftMiddleware()->process($request, $this);
            }
        }

        throw new NotFoundException('Could not find route for path "' . $path . '"', 404);
    }

    /**
     * @param Route $route
     *
     * @throws MiddlewareException
     * @return void
     */
    protected function setFoundMiddleware(Route $route): void
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
}
