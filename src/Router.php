<?php

declare(strict_types=1);

namespace Zaphyr\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\Route\Attributes\Group;
use Zaphyr\Route\Attributes\Route;
use Zaphyr\Route\Contracts\DispatcherInterface;
use Zaphyr\Route\Contracts\RouterInterface;
use Zaphyr\Route\Exceptions\MethodNotAllowedException;
use Zaphyr\Route\Exceptions\MiddlewareException;
use Zaphyr\Route\Exceptions\NotFoundException;
use Zaphyr\Route\Exceptions\RouteException;
use Zaphyr\Route\Traits\MiddlewareAwareTrait;
use Zaphyr\Route\Traits\RouteCollectorTrait;
use Zaphyr\Route\Utils\AttributesResolver;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Router implements RouterInterface
{
    use RouteCollectorTrait;
    use MiddlewareAwareTrait;

    /**
     * @var Route[]
     */
    protected array $routes = [];

    /**
     * @var Group[]
     */
    protected array $groups = [];

    /**
     * @var bool
     */
    protected bool $isPrepared = false;

    /**
     * @param DispatcherInterface $dispatcher
     * @param class-string[]      $controllers
     */
    public function __construct(
        protected DispatcherInterface $dispatcher = new Dispatcher(),
        array $controllers = []
    ) {
        $this->setControllerRoutes($controllers);
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerRoutes(array $controllers): void
    {
        foreach ($controllers as $controller) {
            AttributesResolver::appendRoutes($controller, $this->routes);
            AttributesResolver::appendGroups($controller, $this->groups, $this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $path, array $methods, array|callable|string $callable): Route
    {
        $route = (new Route($path, $methods))->setCallable($callable);

        $this->routes[] = $route;

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function group(string $path, callable $callable): Group
    {
        $group = (new Group($path))->setCallable($callable)->setRouter($this);

        $this->groups[] = $group;

        return $group;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RouteException
     * @throws MiddlewareException
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPrepared) {
            $this->prepareRoutes($request);
        }

        return $this->dispatcher->addMiddlewares($this->getMiddlewareStack())->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return void
     */
    protected function prepareRoutes(ServerRequestInterface $request): void
    {
        foreach ($this->groups as $key => $group) {
            unset($this->groups[$key]);

            $group();
        }

        $uri = $request->getUri();

        foreach ($this->routes as $route) {
            if ($route->getScheme() !== $uri->getScheme()) {
                break;
            }

            if ($route->getHost() !== $uri->getHost()) {
                break;
            }

            if ($route->getPort() !== $uri->getPort()) {
                break;
            }

            $this->dispatcher->addRoute($route);
        }

        $this->isPrepared = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedRoute(string $name): Route
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }

        throw new RouteException('Could not find route with name "' . $name . '"');
    }
}
